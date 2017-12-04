/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   woody_test.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/15 12:02:55 by tktorza           #+#    #+#             */
/*   Updated: 2017/12/04 13:33:55 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"

/*
char *ft_nimp(char *key, int nb)
{
	int size = ft_strlen(key);
	char *test = key;
	int c;
	char *str;
	
	if ((str = (char *)malloc(sizeof(char) * (size + 1))) == NULL)
		return (NULL);
	int i = 1;
	if (nb == 0)
	{
		str[0] = key[size / 3];
		while (i < size)
		{
			str[i] = key[i - 1];
			i++;
		}
	}
	else if (nb == 2)
	{
	i = 2;
		str[2] = key[size / 3];
		while (i < size)
		{
			c = key[i];
			str[i] = (char)(c - 15);
			i++;
		}
	}
	str[i] = '\0';
	return (str);
}

char	*create_key(Elf64_Ehdr *header, Elf64_Shdr *section, uint8_t *data, int *int_key)
{
	char *key;
	char *fake_start;
	int real_start;
	unsigned long long rand_start = &section[header->e_shnum % 3].sh_entsize;

	key =  ft_itoa_base(rand_start, 16);
	//taille de 9 à tj checker
	fake_start = ft_nimp(key, 0);
	real_start = ft_strlen(fake_start);
	//depart à strlen
	key = ft_strjoin(fake_start, key);
	
	// fprintf(stderr, " %llu === %s | %s -- > %s\n", rand_start, fake_start, &key[real_start], key);
	// fprintf(stderr, "key ? %s \n", key);
	for (int i =0;i < ft_strlen(key) + 1;i++)
	{
		printf("%c", key[i]);
	}
	printf("\n");
	fake_start = ft_nimp(fake_start, 2);
		
	for (int i =0;i < ft_strlen(key) + 1;i++)
	{
		printf("%c", key[i]);
	}
	printf("\n");
	
	// fprintf(stderr, "key=%s \n", key);
	
	ft_strjoin(key, fake_start);
	// fprintf(stderr, " key ?%s \n", key);
	// return (key);
	*int_key = 2;
	return ("2");
}
*/

//segment
/*void	deplace_text_section(Elf64_Shdr *section, size_t i, struct stat buf, char *ptr, uint8_t *data)
{
	char str[section[i].sh_size + 1];
	int x = 0;
	for (size_t k = section[i].sh_offset; k < section[i].sh_offset + section[i].sh_size; ++k)
	{
		str[x] = data[k];
		x++;
	}
	size_t k = section[i].sh_offset;
	size_t size = section[i].sh_offset + ft_strlen(ptr);
	while (k <= size)
	{
		data[k] = *ptr;
		*ptr += 1;
		k++;
	}
	section[i].sh_size += size;
	while (k < section[i].sh_offset + section[i].sh_size)
	{
		data[k] = *str;
		*str += 1;
		k++;
	}
}
*/

void	debugg(char *str, unsigned int size)
{
	for (int i = 0;i<size;i++)
	{
		printf("%c", str[i], &str[i]);
	}
	printf("\n");
}

//size doit être signé
void	change_offset(void *ptr, unsigned int v_size, int sign)
{
	//change segments
	Elf64_Ehdr *elf_hdr = (void *)ptr;
    Elf64_Phdr *elf_seg, *text_seg;
	int         n_seg = elf_hdr->e_phnum;
	int			size = v_size;
	if (sign < 0)
		size *= -1;
	size_t i = 0;

	elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_hdr + (unsigned int) elf_hdr->e_phoff);
	while (i < n_seg)
    {
        if (elf_seg->p_type == PT_LOAD && elf_seg->p_flags & 0x011)
			break;
		i++;
		elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_seg + (unsigned int) elf_hdr->e_phentsize);			
	}
	
	i++;
	elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_seg + (unsigned int) elf_hdr->e_phentsize);
	
	while (i < n_seg)
	{
		elf_seg->p_offset += size;
		i++;
		elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_seg + (unsigned int) elf_hdr->e_phentsize);			
	}

	//change sections
	Elf64_Shdr *section;
	uint8_t *data;
	char *sectname;

	i = 0;
	data = ptr;
    elf_hdr = (void *)ptr;
    section = (void *)elf_hdr + elf_hdr->e_shoff;	
	sectname = (char*)(ptr + section[elf_hdr->e_shstrndx].sh_offset);
	while (i < elf_hdr->e_shnum)
	{
		if (ft_strcmp(&sectname[section[i].sh_name], ".text") == 0 && section[i].sh_addr)
			break;
		i++;
	}
	i++;
	while (i < elf_hdr->e_shnum)
	{
		section[i].sh_offset += size;
		i++;
	}
}

void	woody_start(void *ptr, unsigned int size, int fd)
{
	int text_end = 0;
	int gap = 0;
	// char	prev[size];
	// ft_memcpy((void *)prev, ptr, size);
	Elf64_Ehdr *header = (Elf64_Ehdr *)ptr;
	Elf64_Phdr	*t_text_seg = elf_find_gap(ptr, size, &text_end, &gap);
	Elf64_Addr	base = t_text_seg->p_vaddr;
	Elf64_Addr	e_entry = header->e_entry;
	struct stat buf;
	int		fd_infect;
	void		*inf_addr = open_decrypt(&buf, &fd_infect);
	Elf64_Shdr *virus_text = elf_find_section(inf_addr, ".text");
	char *woody = (char *)malloc(sizeof(char) * (virus_text->sh_size + 1 + size));
	
	if (woody == NULL)
	{
		fprintf (stderr, "Error malloc of woody char *.\n");
		exit (1);
	}
	
	Elf64_Shdr *bin_text = elf_find_section(ptr, ".text");
	
	printf("physical == %llx base == %llx ? v_addr(.text sec) == %llx | e_entry = %llx\n", t_text_seg->p_paddr, (void *)base, bin_text->sh_addr, header->e_entry);
	
    printf ("+ .text segment gap at offset 0x%x(0x%x bytes available)\n", text_end, gap);
  //on modifie ptr pour le copier dans woody pour ensuite le restaurer
	t_text_seg->p_memsz += virus_text->sh_size;
	t_text_seg->p_filesz += virus_text->sh_size;
	header->e_entry = (Elf64_Addr) (base + text_end);
	header->e_shoff += virus_text->sh_size;
	//declaller offsets des sections autres
	change_offset(ptr, virus_text->sh_size, -1);
	
	printf ("+ Payload .text section found at %llx (%llx bytes)\n", 
	virus_text->sh_offset, virus_text->sh_size);

	if (virus_text->sh_size > gap)
	{
		fprintf (stderr, "- Payload to big, cannot infect file.\n");
		exit (1);
	}
	ft_memcpy(woody, ptr, text_end);
	ft_memcpy(&woody[text_end + 1], inf_addr + virus_text->sh_offset, virus_text->sh_size);
	ft_memcpy(&woody[text_end + virus_text->sh_size + 1], ptr + text_end, size - text_end);
	// debugg((char *)(ptr + text_end), virus_text->sh_size);
	// debugg((char *)(inf_addr + virus_text->sh_offset), virus_text->sh_size);
    // return text_seg;
    
	// key = create_key(header, section, data, &int_key);
	// loop_section_offset_free_for_decrypt(header, section, sectname, data);
	printf("base + text_end == %llx | e_entry = %llx\n", base + text_end, e_entry);
	
	elf_mem_subst(&woody[text_end], virus_text->sh_size, 0x11111111, e_entry);
	// printf("base + text_end == %llx | e_entry = %llx\n", base + text_end, header->e_entry);
	// close(fd);
	// close(fd_infect);
	//on restaure ptr
	t_text_seg->p_memsz -= virus_text->sh_size;
	t_text_seg->p_filesz -= virus_text->sh_size;
	header->e_entry = e_entry;
	header->e_shoff -= virus_text->sh_size;
	change_offset(ptr, virus_text->sh_size, 1);
	
	open_woody((void *)woody, size + virus_text->sh_size + 1, fd, fd_infect);
	free(woody);
}
