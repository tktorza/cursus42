/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   elf_functions.c                                    :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/23 17:18:38 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/23 17:29:01 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"

Elf64_Phdr *elf_find_gap(void *ptr, int size, int *p, int *len)
{
    Elf64_Ehdr *elf_hdr = (void *)ptr;
    Elf64_Phdr *elf_seg, *text_seg;
    int         n_seg = elf_hdr->e_phnum;
    int text_end, gap=size;
    // struct stat buf;
    // char    *infect_addr;
    
    // infect_addr = (char *)open_decrypt(&buf, &gap);
    elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_hdr + (unsigned int) elf_hdr->e_phoff);

    for (size_t i = 0;i < n_seg;i++)
    {
        if (elf_seg->p_type == PT_LOAD && elf_seg->p_flags & 0x011)
        {
            printf("Segment .text found: #%lu\n", i);
			text_seg = elf_seg;
			//fin de seg text
            text_end = text_seg->p_offset + text_seg->p_filesz;
        }
        else
        {
			//si gap < size du file
          if (elf_seg->p_type == PT_LOAD && (elf_seg->p_offset - text_end) < gap) 
            {
				gap = elf_seg->p_offset - text_end;
              printf ("   * Found LOAD segment (#%d) close to .text (offset: 0x%x) --> gap(#%d)\n", i, (unsigned int)elf_seg->p_offset, gap);
            }
		}
		//on increment de elf_seg
          elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_seg + (unsigned int) elf_hdr->e_phentsize);
	}
	
    *p = text_end;
    *len = gap;

    return (text_seg);
}

Elf64_Shdr *elf_find_section(void *ptr, char *name)
{
	Elf64_Ehdr *header;
	Elf64_Shdr *section;
	uint8_t *data;
	char *sectname;

	data = ptr;
    header = (void *)ptr;
    section = (void *)header + header->e_shoff;	
	sectname = (char*)(ptr + section[header->e_shstrndx].sh_offset);

	printf ("+ %d section in file. Looking for section '%s'\n", 
		header->e_shnum, name);
	
	for (size_t i = 0; i < header->e_shnum; i++)
	  {
		if (ft_strcmp(&sectname[section[i].sh_name], ".text") == 0 && section[i].sh_addr)
			return (&section[i]);
	  }
	return (NULL);
}

int		elf_mem_subst(void *m, int len, long pat, long val)
{
  unsigned char *p = (unsigned char*)m;
  long v;
  int i, r;

  for (i = 0; i < len; i++)
  {
	  v = *((long *)(p + i));
	  r = v ^ pat;

	  if (r == 0)
	  {
		  printf("+ Pattern %lx found at offset %d -> %lx\n", pat, i, val);
		  *((long *)(p + i)) = val;
		  return 0;
	  }
  }
  return -1;
}