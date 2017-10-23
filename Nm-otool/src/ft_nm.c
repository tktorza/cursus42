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

#include "../includes/nm_tool.h"

#define ERROR(name)                                                                   \
    ft_putstr(ft_strjoin(ft_strjoin("nm: ", name), " No such file or directory.\n")); \
    return (-1)

void nm(char *ptr, t_symtab *symt)
{
    int magic_number;
    //on prend le premier octet que l'on convertit en int
    magic_number = *(int *)ptr;
    //regarder si 64bits
    if (magic_number == MH_MAGIC_64)
        handle_64(ptr, symt);
    else if (magic_number == MH_MAGIC)
        handle_32(ptr, symt);
}

int main(int ac, char **av)
{
    int fd;
    void *ptr;
    struct stat buf;
    t_symtab symt = {0, 0, 0, -1, 0, 1, 0};
    
    if (ac < 2)
        av[1] = "a.out\0";
    if ((fd = open(av[1], O_RDONLY)) != -1)
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

        nm(ptr, &symt);
    }
    else
        ERROR(av[1]);
    return 0;
}