/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   woody.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/15 12:02:55 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/17 16:40:33 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"
#include "../includes/elf.h"

char *ft_nimp(char *key, int nb)
{
	int size = ft_strlen(key);
	char *str = (char *)malloc((sizeof(char) * size) + 1);

	int i = 1;
	if (nb == 0)
	{
		str[0] = key[ft_strlen(key) % 3];
		while (i < size)
		{
			str[i] = key[i - 1];
			i++;
		}
	}
	else if (nb == 2)
	{
		str[0] = key[ft_strlen(key) % 3];
		while (i < size)
		{
			str[i] = (char)(key[i] - (15 % (i + 3)));
			i++;
		}
	}
	str[i] = '\0';
	return (str);
}

char	*create_key(Elf64_Ehdr *header, Elf64_Shdr *section, uint8_t *data)
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
	printf(" %llu === %s | %s -- > %s\n", rand_start, fake_start, &key[real_start], key);
	ft_printf("key ? %s \n", key);
	fake_start = ft_nimp(key, 2);
	ft_strjoin(key, fake_start);
	ft_printf(" key ?%s \n", key);
	return (key);
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

void	encrypt_text(uint8_t *data, size_t k)
{
	data[k] = data[k] + 1;
}

void	woody_start(void *ptr, unsigned int size)
{
	Elf64_Ehdr *header;
    Elf64_Shdr *section;
	char *sectname;
	uint8_t *data;

	data = ptr;
    header = (void *)ptr;
    section = (void *)header + header->e_shoff;	
	sectname = (char*)(ptr + section[header->e_shstrndx].sh_offset);
	create_key(header, section, data);
	/*
    for (size_t i = 0; i < header->e_shnum;i++)
    {
		if (ft_strcmp(&sectname[section[i].sh_name], ".text") == 0 && section[i].sh_addr)
		{
			int j = 1;
			for (size_t k = section[i].sh_offset; k < section[i].sh_offset + section[i].sh_size; ++k)
			{
				encrypt_text(data, k);				
				if (!(j % 4))
					printf("%02x ", data[k]);
				else
					printf("%02x", data[k]);
				if (!(j % 16))
					printf("\n");
				++j;
			}

			printf("\nname: %s\n", &sectname[section[i].sh_name]);
			printf("size: %llu\n", section[i].sh_size);
			printf("addr: %#llx\n", section[i].sh_addr);
		}	
	}
	open_woody(ptr, size);*/
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