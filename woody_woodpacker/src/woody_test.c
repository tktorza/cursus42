/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   woody_test.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/15 12:02:55 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/29 17:36:28 by tktorza          ###   ########.fr       */
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

void	woody_start(void *ptr, unsigned int size, int fd)
{
	unsigned int data_end = 0;
	int gap = 0;
	char	prev[size];
	Elf64_Addr e_entry; 

	// ft_memcpy((void *)prev, ptr, size);
	Elf64_Ehdr *header = (Elf64_Ehdr *)ptr;
	Elf64_Phdr	*data_seg = elf_find_gap(ptr, &data_end/*, size, &gap*/);
	Elf64_Addr	base = data_seg->p_vaddr;
	//test programme header segment
	// listing_seg(ptr);

	printf("base == %p | e_entry = %llx\n", (void *)base, header->e_entry);
    printf ("+ .text segment gap at offset 0x%x(0x%x bytes available)\n", data_end, gap);
  
	struct stat buf;
	int		fd_infect;
	void		*inf_addr = open_decrypt(&buf, &fd_infect);
	Elf64_Shdr *p_text_sec = elf_find_section(inf_addr, ".text");
	Elf64_Shdr *bss_sec = elf_find_section(ptr, ".bss");
	bss_sec->sh_addr += (p_text_sec->sh_size + 7);
	bss_sec->sh_offset += (p_text_sec->sh_size + 7);

	//mettre les flags sur le segment .text
	printf ("+ Payload .text section found at %llx (%llx bytes)\n", 
	p_text_sec->sh_offset, p_text_sec->sh_size);

	data_seg->p_flags = PF_R | PF_W | PF_X;
	data_seg->p_memsz += p_text_sec->sh_size;
	data_seg->p_filesz += p_text_sec->sh_size;
	e_entry = header->e_entry;
	header->e_entry = data_seg->p_vaddr + data_seg->p_filesz + (data_seg->p_memsz - data_seg->p_filesz);
	header->e_shoff += p_text_sec->sh_size + 7;
	//decaller chaque offset des sections apres data de bss_size + p_text_sec->sh_size
	// boucle_after_data_segment();
	// write(fd, "\x48\xc7\x44\x24\x08", 5); /* movq [rsp + 8], */


	// header->e_shoff += (data_seg->p_memsz - data_seg->p_filesz) + p_text_sec->sh_size;

	/*if (p_text_sec->sh_size > gap)
	{
		fprintf (stderr, "- Payload to big, cannot infect file.\n");
		exit (1);
	}*/
	/* Copy payload in the segment padding area */
	// ft_memmove (ptr + text_end, inf_addr + p_text_sec->sh_offset, p_text_sec->sh_size);
	printf("It's ok\n");

	ft_memmove (ptr + data_end/* + (data_seg->p_memsz - data_seg->p_filesz)*/,
	inf_addr + p_text_sec->sh_offset, p_text_sec->sh_size);
	printf("It's ok 2\n");
	
	// debugg((char *)(ptr + text_end), p_text_sec->sh_size);
	// debugg((char *)(inf_addr + p_text_sec->sh_offset), p_text_sec->sh_size);
    // return text_seg;
    
	// key = create_key(header, section, data, &int_key);
	// loop_section_offset_free_for_decrypt(header, section, sectname, data);
	// printf("base + text_end == %llx | e_entry = %llx\n", base + text_end, header->e_entry);
	
	elf_mem_subst(ptr + data_end, p_text_sec->sh_size, 0x11111111, e_entry);
	printf("It's ok 3\n");
	
	// printf("base + text_end == %llx | e_entry = %llx\n", base + text_end, header->e_entry);
	// header->e_entry = (Elf64_Addr) (base + text_end);
	// header->e_shoff += p_text_sec->sh_size;
	// close(fd);
	// close(fd_infect);
	open_woody(ptr, size, fd, fd_infect);
}
