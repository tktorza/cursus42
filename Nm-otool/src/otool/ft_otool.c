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

int				type_bin(char *ptr, char *file, t_symtab *symt)
{
	uint32_t magic_number;

	magic_number = *(uint32_t *)ptr;
	if (magic_number == MH_MAGIC_64)
		handle_o_64(ptr, file, symt);
	else if (magic_number == MH_MAGIC)
		handle_o_32(ptr, file, symt);
	else if (ft_strncmp(ptr, ARMAG, SARMAG) == 0)
		handle_o_lib(ptr, file, symt);
	else if (magic_number == FAT_MAGIC || magic_number == FAT_CIGAM)
		handle_fat(ptr, file, symt);
	else
	{
		ft_printf("chwvibcejwbocuwej%s\n", ptr);
		return (-1);
	}
	return (1);
}

static void		symt_init(t_symtab *symt)
{
	symt->data = 0;
	symt->bss = 0;
	symt->text = 0;
	symt->i = -1;
	symt->j = 0;
	symt->ns = 1;
	symt->exec = 0;
	symt->otool = 0;
	symt->x = 0;
	symt->size = 0;
	symt->size_name = 0;
}

int				ft_otool(char *av)
{
	int			fd;
	void		*ptr;
	struct stat	buf;
	t_symtab	symt;

	symt_init(&symt);
	if ((fd = open(av, O_RDONLY)) != -1)
	{
		if (fstat(fd, &buf) < 0)
		{
			ERROR("ok");
		}
		if ((ptr = mmap(0, buf.st_size, PROT_READ, MAP_PRIVATE, fd, 0))
		== MAP_FAILED)
		{
			ERROR("ok");
		}
		symt.exec = ((buf.st_mode & S_IXUSR) ? 1 : 0);
		return (type_bin(ptr, av, &symt));
	}
	else
		ERROR(av);
}

int				main(int ac, char **av)
{
	int i;

	i = 0;
	if (ac < 2)
		av[1] = "a.out\0";
	while (++i < ac)
		if (ft_otool(av[i]) == -1)
			break ;
	return (0);
}
