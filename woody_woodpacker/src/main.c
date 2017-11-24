/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   main.c                                             :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/14 16:39:47 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/24 13:01:22 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"
#include "../includes/elf.h"

void        print_error(char *file, char *str)
{
    printf("ft_nm: %s: %s.\n", file, str);
    exit (1);
}

void 	decypt()
{
	printf("...WOODY...\n");
}

void	open_woody(void *ptr, unsigned int size, int fd1, int fd2)
{
	int	fd;

	if ((fd = open("./woody", O_CREAT | O_RDWR | O_TRUNC, 0777)) <= 0)
		print_error("woody", "Error on open");
    write(fd, ptr, size);
	close(fd);
	close(fd1);
	close(fd2);
	printf("All is ok and files are close, test?\n");
}

void	encrypt_text(uint8_t *data, size_t k, int key)
{
	// printf("keyyyy ===== %d\n", key);
	data[k] = data[k] + key;
}

void    *open_decrypt(struct stat *buf, int *fd/*, int *gap*/)
{
    void 				*ptr;

    if ((*fd = open("./src/test", O_APPEND | O_RDWR, 0)) < 0)
		print_error("./src/test", "No such file or directory");
	if (fstat(*fd, buf) < 0)
		print_error("./src/test", "Error with fstat");
	if ((ptr = mmap(0, buf->st_size,  PROT_READ | PROT_WRITE | PROT_EXEC, MAP_SHARED, *fd, 0))
	== MAP_FAILED)
        print_error("./src/test", "Is a directory");
    /**gap = buf->st_size;*/
	return (ptr);
}

static int			is_elf64(char *ptr)
{
	if (ptr[0] == ELFMAG0 && ptr[1] == ELFMAG1 && ptr[2] == ELFMAG2 &&
		ptr[3] == ELFMAG3 && ptr[4] == ELFCLASS64)
		return (1);
	return (0);
}

static void        *get_ptr(char *filename, unsigned int *size, int *fd)
{
	struct stat			buf;
	void 				*ptr;

	if ((*fd = open(filename, O_APPEND | O_RDWR, 0)) < 0)
		print_error(filename, "No such file or directory");
	if (fstat(*fd, &buf) < 0)
		print_error(filename, "Error with fstat");
	if ((ptr = mmap(0, buf.st_size, PROT_READ | PROT_WRITE | PROT_EXEC, MAP_SHARED, *fd, 0))
	== MAP_FAILED)
		print_error(filename, "Is a directory");
	*size = buf.st_size;
	return (ptr);
}

int         main(int ac, char **av)
{
	void 				*ptr;
	unsigned int		size;
	int					fd;
	
	if (ac == 2)
	{
		ptr = get_ptr(av[1], &size, &fd);
		if (!is_elf64((char *)ptr))
			print_error(av[1], "This is not a elf64");
		woody_start(ptr, size, fd);
	}
	else
		printf("Error: %s <filename>\n", av[0]);
	return (0);
}