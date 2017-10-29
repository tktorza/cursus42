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

static void		symt_init(t_symtab *symt, int bonus)
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
	symt->lib = (symt && symt->lib && symt->lib != 0) ? symt->lib: 0;
	symt->bonus = bonus;
}

int				type_bin(char *ptr, char *file, t_symtab *symt, int bonus)
{
	uint32_t magic_number;

	symt_init(symt, bonus);	
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
		return (-1);
	return (1);
}

int				ft_otool(char *av, int bonus)
{
	int			fd;
	void		*ptr;
	struct stat	buf;
	t_symtab	symt;
	
	if ((fd = open(av, O_RDONLY)) != -1)
	{
		if (fstat(fd, &buf) < 0)
		{
			ERROR_OTOOL(": Error with fstat");
		}
		if ((ptr = mmap(0, buf.st_size, PROT_READ, MAP_PRIVATE, fd, 0))
		== MAP_FAILED)
		{
			ERROR_OTOOL("Is a directory");
		}
		symt.exec = ((buf.st_mode & S_IXUSR) ? 1 : 0);
		return (type_bin(ptr, av, &symt, bonus));
	}
	else
		ERROR_OTOOL(ft_strjoin(av, ": No such file or directory."));
}

int		is_bonus(char **s, int *i, int ac)
{
	int bonus;

	bonus = 0;
	while (*i < ac)
	{
		if (ft_strcmp(s[*i], "-t") == 0)
			bonus += ((bonus & NO_SORT) == 0 ) ? 0 : 0;
		else if (ft_strcmp(s[*i], "-d") == 0)
			bonus += ((bonus & DATA_OT) == 0 ) ? DATA_OT : 0;
		else if (ft_strcmp(s[*i], "-b") == 0)
			bonus += ((bonus & BSS_OT) == 0 ) ? BSS_OT : 0;
		else if (ft_strcmp(s[*i], "-all") == 0)
			bonus += ((bonus & ALL_OT) == 0 ) ? ALL_OT : 0;
		else
			break ;
		*i += 1;
	}
	return (bonus);
}

int			main(int ac, char **av)
{
	int	i;
	int bonus;

	i = 1;
	if (ac < 2)
		av[1] = "a.out\0";
	else
		bonus = is_bonus(av, &i, ac);
	i--;
	while (++i < ac)
		if (ft_otool(av[i], bonus) == -1)
			break ;
	return (0);
}
