/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   woody.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/15 12:02:55 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/17 17:23:40 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"
#include "../includes/elf.h"

char *ft_nimp(char *key, int nb)
{
	int size = ft_strlen(key);
	char *test = key;
	int c;
	char *str = (char *)malloc(sizeof(char) * (size + 1));
	fprintf(stderr, "after 0 = %s\n", key);
	
	int i = 1;
	if (nb == 0)
	{
		str[0] = key[ft_strlen(key) / 3];
		while (i < size)
		{
			str[i] = key[i - 1];
			i++;
		}
	}
	else if (nb == 2)
	{
	fprintf(stderr, "1 = %s\n", key);
	
		str[0] = key[size / 3];
	fprintf(stderr, "2 = %s | %d\n", key, size / 3);
	
		while (i < size)
		{
			c = key[i];
			str[i] = (char)(c - 15);
			i++;
		}
	fprintf(stderr, "3 = %s\n", key);
	
	}
	str[i] = '\0';
	fprintf(stderr, "KEY = %s\n", key);
	return (str);
}

char	*create_key(Elf64_Ehdr *header, Elf64_Shdr *section, uint8_t *data, int *int_key)
{
	char *key;
	char *fake_start;
	int real_start;
	unsigned long long rand_start = &section[header->e_shnum % 3].sh_entsize;

	key =  ft_itoa_base_maj(rand_start, 16);
	fake_start = ft_nimp(key, 0);
	real_start = ft_strlen(fake_start);
	//depart à strlen
	key = ft_strjoin(fake_start, key);
	// fprintf(stderr, " %llu === %s | %s -- > %s\n", rand_start, fake_start, &key[real_start], key);
	// fprintf(stderr, "key ? %s \n", key);
	fake_start = ft_nimp(key, 2);
	// fprintf(stderr, "1 key ?%s \n", key);
	
	ft_strjoin(key, fake_start);
	// fprintf(stderr, " key ?%s \n", key);
	// return (key);
	*int_key = 2;
	return ("2");
}
void        print_error(char *file, char *str)
{
    printf("ft_nm: %s: %s.\n", file, str);
    exit (1);
}

void 	decypt()
{
	printf("...WOODY...\n");
	
}


void	open_woody(void *ptr, unsigned int size)
{
	int	fd;

	if ((fd = open("./woody", O_CREAT | O_RDWR | O_TRUNC, 0777)) <= 0)
		print_error("woody", "Error on open");
	write(fd, ptr, size);
	close(fd);
}

void	encrypt_text(uint8_t *data, size_t k, int key)
{
	// printf("keyyyy ===== %d\n", key);
	data[k] = data[k] + key;
}

Elf32_Addr	loop_section_offset_free_for_decrypt(Elf64_Ehdr *header, Elf64_Shdr *section, char *sectname, size_t size_to_search)
{
	void *ptr;
	for (size_t i = 0; i < header->e_shnum;i++)
    {
		if (section[i].sh_offset => size_to_search /*+ appel vers previous virtual address*/)
		{
			ptr = (void *)(section[i]) + section[i].sh_size;
			//copier decrypt bit par bit for (int i = 0;ptr + i; i < size);
			//copier appel vers header.e_entry
			/*printf("\nname: %s\n", &sectname[section[i].sh_name]);
			printf("size: %llu\n", section[i].sh_size);
			printf("addr: %#llx\n", section[i].sh_addr);
			printf("offset: %#llu\n", section[i].sh_offset);
		*/	//header->e_entry = loop_section_offset_free_for_decrypt(header->e_entry);
			//ajouter decrypt à la fin
			break;
		}
	}
	return (ptr);
}

void	woody_start(void *ptr, unsigned int size)
{
	Elf64_Ehdr *header;
    Elf64_Shdr *section;
	char *sectname;
	uint8_t *data;
	char *key;
	int int_key;

	data = ptr;
    header = (void *)ptr;
    section = (void *)header + header->e_shoff;	
	sectname = (char*)(ptr + section[header->e_shstrndx].sh_offset);
	key = create_key(header, section, data, &int_key);
    for (size_t i = 0; i < header->e_shnum;i++)
    {
		if (ft_strcmp(&sectname[section[i].sh_name], ".text") == 0 && section[i].sh_addr)
		{
			/*
			int j = 1;
			for (size_t k = section[i].sh_offset; k < section[i].sh_offset + section[i].sh_size; ++k)
			{
				encrypt_text(data, k, int_key);
				if (!(j % 4))
					printf("%02x ", data[k]);
				else
					printf("%02x", data[k]);
				if (!(j % 16))
					printf("\n");
				++j;
			}
*/

			printf("\nname: %s\n", &sectname[section[i].sh_name]);
			printf("size: %llu\n", section[i].sh_size);
			printf("addr: %#llx\n", section[i].sh_addr);
			printf("offset: %#llu\n", section[i].sh_offset);
			//header->e_entry = loop_section_offset_free_for_decrypt(header->e_entry);
			//ajouter decrypt à la fin
			break;
		}	
	}

	open_woody(ptr, size);
}

static int			is_elf64(char *ptr)
{
	if (ptr[0] == ELFMAG0 && ptr[1] == ELFMAG1 && ptr[2] == ELFMAG2 &&
		ptr[3] == ELFMAG3 && ptr[4] == ELFCLASS64)
		return (1);
	return (0);
}

static void        *get_ptr(char *filename, unsigned int *size)
{
	struct stat			buf;
	int					fd;
	void 				*ptr;

	if ((fd = open(filename, O_RDONLY)) < 0)
		print_error(filename, "No such file or directory");
	if (fstat(fd, &buf) < 0)
		print_error(filename, "Error with fstat");
	if ((ptr = mmap(0, buf.st_size, PROT_READ | PROT_WRITE, MAP_PRIVATE, fd, 0))
	== MAP_FAILED)
		print_error(filename, "Is a directory");
	*size = buf.st_size;
	return (ptr);
}


int         main(int ac, char **av)
{
	void 				*ptr;
	unsigned int		size;
	if (ac == 2)
	{
		ptr = get_ptr(av[1], &size);
		if (!is_elf64((char *)ptr))
			print_error(av[1], "This is not a elf64");
		woody_start(ptr, size);
	}
	else
		printf("Error: %s <filename>\n", av[0]);
	return (0);
}