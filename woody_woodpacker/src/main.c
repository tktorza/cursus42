/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   main.c                                             :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/14 16:39:47 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/17 15:46:28 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"
#include "../includes/elf.h"

void        print_error(char *file, char *str)
{
    printf("ft_nm: %s: %s.\n", file, str);
    exit (1);
}

static int            is_elf64(char *ptr)
{
    if (ptr[0] == ELFMAG0 && ptr[1] == ELFMAG1 && ptr[2] == ELFMAG2 &&
        ptr[3] == ELFMAG3 && ptr[4] == ELFCLASS64)
    {
		printf("Success\n");

        return (1);
    }
    return (0);
}

static void        *get_ptr(char *filename, unsigned int *size)
{
    struct stat            buf;
    int                    fd;
    void                 *ptr;

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
    void                 *ptr;
    unsigned int        size;
    if (ac == 2)
    {
        ptr = get_ptr(av[1], &size);
        if (!is_elf64((char *)ptr))
			print_error(av[1], "This is not a elf64");
		else
			woody_start(ptr);
    }
    else
        printf("Error: %s <filename>\n", av[0]);
    return (0);
}