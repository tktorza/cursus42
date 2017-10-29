/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_nm.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/18 13:15:23 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 12:27:05 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

static void	symt_init(t_symtab *symt, int bonus)
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
	symt->bonus = bonus;
	symt->lib = (symt && symt->lib && symt->lib != 0) ? symt->lib : 0;
}

int			type_bin(char *ptr, char *file, t_symtab *symt, int bonus)
{
	uint32_t	magic_number;

	symt_init(symt, bonus);
	magic_number = *(uint32_t *)ptr;
	if (magic_number == MH_MAGIC_64)
		handle_64(ptr, symt);
	else if (magic_number == MH_MAGIC)
		handle_32(ptr, symt);
	else if (ft_strncmp(ptr, ARMAG, SARMAG) == 0)
		handle_lib(ptr, file, symt);
	else if (magic_number == FAT_MAGIC || magic_number == FAT_CIGAM)
		handle_fat(ptr, file, symt);
	else
		return (-1);
	return (1);
}

int			ft_nm(char *av, int bonus)
{
	int				fd;
	void			*ptr;
	struct stat		buf;
	t_symtab		symt;

	if ((fd = open(av, O_RDONLY)) != -1)
	{
		if (fstat(fd, &buf) < 0)
		{
			ERROR_NM(ft_strjoin(av, ": Error with fstat"));
		}
		if ((ptr = mmap(0, buf.st_size, PROT_READ, MAP_PRIVATE, fd, 0)) \
		== MAP_FAILED)
		{
			ERROR_NM(ft_strjoin(av, "Is a directory"));
		}
		symt.exec = ((buf.st_mode & S_IXUSR) ? 1 : 0);
		return (type_bin(ptr, av, &symt, bonus));
	}
	else
		ERROR_NM(ft_strjoin(av, ": No such file or directory."));
}

int		is_bonus(char **s, int *i, int ac)
{
	int bonus;

	bonus = 0;
	while (*i < ac)
	{
		if (ft_strcmp(s[*i], "-p") == 0)
			bonus += ((bonus & NO_SORT) == 0 ) ? NO_SORT : 0;
		else if (ft_strcmp(s[*i], "-u") == 0)
			bonus += ((bonus & UNDEFINED) == 0 ) ? UNDEFINED : 0;
		else if (ft_strcmp(s[*i], "-U") == 0)
			bonus += ((bonus & NOT_UNDEFINED) == 0 ) ? NOT_UNDEFINED : 0;
		else if (ft_strcmp(s[*i], "-d") == 0)
			bonus += ((bonus & DECIMAL) == 0 ) ? DECIMAL : 0;
		else if (ft_strcmp(s[*i], "-j") == 0)
			bonus += ((bonus & SYMBOL_NAME) == 0 ) ? SYMBOL_NAME : 0;
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
		if (ft_nm(av[i], bonus) == -1)
			break ;
	return (0);
}
