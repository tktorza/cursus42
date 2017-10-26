/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_nm.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/18 13:15:23 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 13:37:23 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

#define ERROR(name)                                                                   \
    ft_putstr(ft_strjoin(ft_strjoin("nm: ", name), " No such file or directory.\n")); \
    return (-1)

int type_bin(char *ptr, char *file, t_symtab *symt)
{
    uint32_t magic_number;
    //on prend le premier octet que l'on convertit en int
    magic_number = *(uint32_t *)ptr;
    //regarder si 64bits
    if (magic_number == MH_MAGIC_64)
        handle_64(ptr, symt);
    else if (magic_number == MH_MAGIC)
        handle_32(ptr, symt);
    else if (ft_strncmp(ptr, ARMAG, SARMAG) == 0)
        handle_lib(ptr, file, symt);
    else if (magic_number == FAT_MAGIC || magic_number == FAT_CIGAM/*a lenvers*/)
        handle_fat(ptr, file, symt);
    else
    {
        ft_printf("%s\n", ptr);
        return (-1);
    }
    return (1);
}

int ft_otool(char *av)
{
    int fd;
    void *ptr;
    struct stat buf;
    t_symtab symt = {0, 0, 0, -1, 0, 1, 0, 0};

    if ((fd = open(av, O_RDONLY)) != -1)
    {
        if (fstat(fd, &buf) < 0)
        {
            ERROR("ok");
        }
        if ((ptr = mmap(0, buf.st_size, PROT_READ, MAP_PRIVATE, fd, 0)) == MAP_FAILED)
        {
            ERROR("ok");
        }
        symt.exec = ((buf.st_mode & S_IXUSR) ? 1 : 0);
        return (type_bin(ptr, av, &symt));
    }
    else
        ERROR(av);
}

int main(int ac, char **av)
{
    int i;
    
    i = 0;
    if (ac < 2)
        av[1] = "a.out\0";
    while (++i < ac)
        if (ft_otool(av[i]) == -1)
            break;
    return 0;
}