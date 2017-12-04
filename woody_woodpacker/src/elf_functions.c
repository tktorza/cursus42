/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   elf_functions.c                                    :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/23 17:18:38 by tktorza           #+#    #+#             */
/*   Updated: 2017/12/04 15:56:28 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"

Elf64_Phdr *elf_find_gap(void *ptr, int size, int *p, int *len)
{
    Elf64_Ehdr *header = (Elf64_Ehdr *)ptr;
    Elf64_Phdr *seg, *text_seg;
    int         n_seg = header->e_phnum;
    int text_end, gap=size;
    // struct stat buf;
    // char    *infect_addr;
    
    // infect_addr = (char *)open_decrypt(&buf, &gap);
    seg = (Elf64_Phdr *) (ptr + (unsigned int)header->e_phoff);

    for (int i = 0;i < n_seg;i++)
    {
        if (seg->p_type == PT_LOAD && seg->p_flags & 0x011)
        {
            fprintf(stderr, "Segment .text found: #%lu | vaddr (%llx) physical(%llx)\n", i, seg->p_vaddr, seg->p_paddr);
            text_seg = seg;
			//fin de seg text
            text_end = text_seg->p_offset + text_seg->p_filesz;
        }
        /*else
        {
			//si gap < size du file
          if (seg->p_type == PT_LOAD && (seg->p_offset - text_end) < gap) 
            {
				gap = seg->p_offset - text_end;
              printf ("   * Found LOAD segment (#%d) close to .text (offset: 0x%x) --> gap(#%d)\n", i, (unsigned int)seg->p_offset, gap);
            }
		}*/
        //on increment de seg
        seg++;
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
		if (ft_strcmp(&sectname[section[i].sh_name], name) == 0 && section[i].sh_addr)
			return (&section[i]);
	  }
	return (NULL);
}

int		elf_mem_subst(void *m, int len, long pat, unsigned long long val)
{
  unsigned char *p = (unsigned char*)m;
  unsigned long long v;
  int i, r;

  for (i = 0; i < len; i++)
  {
	  v = *((unsigned long long *)(p + i));
	  r = v ^ pat;

	  if (r == 0)
	  {
          printf("+ Pattern %lx found at offset %d -> %lx\n", pat, i, val);
          *((unsigned long long *)(p + i)) = val;
		  return 0;
	  }
  }
  return -1;
}