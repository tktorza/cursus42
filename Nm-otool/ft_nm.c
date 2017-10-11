#include "libft/includes/libft.h"
#include <fcntl.h>
#include <stdio.h>
#include <stdlib.h>
#define ERROR(name) ft_putstr(ft_strjoin(ft_strjoin("nm: ", name), " No such file or directory.\n"))

int     main(int ac, char **av)
{
    int fd;

    if (ac < 2)
        av[1] = "a.out\0";
    if ((fd = open(av[1], O_RDONLY)) != -1)
    {
        
    }
    else
        ERROR(av[1]);
    return 0;
}