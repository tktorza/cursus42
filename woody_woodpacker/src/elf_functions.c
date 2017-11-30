/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   elf_functions.c                                    :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/23 17:18:38 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/30 12:07:11 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"

void    listing_seg(void *ptr)
{
    Elf64_Ehdr *elf_hdr = (void *)ptr;
    Elf64_Phdr* elf_seg = (Elf64_Phdr *)(ptr + elf_hdr->e_phoff);

    for (size_t i = 0;i < elf_hdr->e_phnum;i++)
    {
            printf("SEGMENT : %d\n\n", elf_seg->p_type);
          elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_seg + (unsigned int) elf_hdr->e_phentsize);          
	}
}

Elf64_Phdr      *elf_find_gap(void *ptr, unsigned int *data_end/*, int *size, int *len*/)
{
    Elf64_Ehdr *elf_hdr = (void *)ptr;
    Elf64_Phdr* elf_seg, *data_seg;
    int         n_seg = elf_hdr->e_phnum;
    // int text_end, gap=size;
    // struct stat buf;
    // char    *infect_addr;
    
    // infect_addr = (char *)open_decrypt(&buf, &gap);
    elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_hdr + (unsigned int) elf_hdr->e_phoff);

    for (size_t i = 0;i < n_seg;i++)
    {
        
        if (elf_seg->p_type == PT_LOAD && elf_seg->p_flags == (PF_R | PF_W))
        {
            printf("Segment found: #%lu | %llx | %llx | %lu |  elf_seg->p_type = %d | %dg\n", i, elf_seg->p_paddr, elf_seg->p_vaddr, elf_seg->p_offset, elf_seg->p_type, elf_seg->p_flags);
            
            data_seg = elf_seg;
            //SEG DATA
            *data_end = data_seg->p_offset + data_seg->p_filesz;
            return (elf_seg);
            // printf("Segment .text found: #%lu | p_padrr = %llu &&p_vaddr = %llu\n", i, elf_seg->p_paddr, elf_seg->p_vaddr, elf_seg->p_offset, elf_seg->p_type, elf_seg->p_flags);
        }
        // else
        //{
			//si gap < size du file
        //   if (elf_seg->p_type == PT_LOAD && (elf_seg->p_offset - data_end) < gap) 
        //     {
		// 		gap = elf_seg->p_offset - data_end;
        //       printf ("   * Found LOAD segment (#%d) close to .text (offset: 0x%x) --> gap(#%d)\n", i, (unsigned int)elf_seg->p_offset, gap);
        //     }
		//}
		//on increment de elf_seg
          elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_seg + (unsigned int) elf_hdr->e_phentsize);
	}
    // *len = gap;

    return (data_seg);
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