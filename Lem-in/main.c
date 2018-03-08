/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   main.c                                             :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:56:01 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 16:27:07 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

int			ft_error(void)
{
	ft_putendl("ERROR");
	return (-1);
}

static int	next(t_sett *origin, t_file *beggin, t_bloc *bloc)
{
	g_sett_origin = ft_cop_listb(origin);
	if (check(origin) == -1)
		return (ft_error());
	beggin = ft_parsing(origin);
	if (g_origin == NULL)
		return (ft_error());
	g_origin = short_link();
	if (g_origin == NULL)
		return (ft_error());
	doublon_dell();
	bloc = ft_calling();
	if (bloc == NULL)
		return (ft_error());
	ft_affich(NULL, bloc);
	ft_display(bloc);
	return (0);
}

static int	ft_cut_main(t_sett *origin, t_file *beggin,
	t_bloc *bloc, t_sett *set)
{
	int		i;
	int		j;

	j = 0;
	i = 0;
	while ((i = get_next_line(0, &set->str)))
	{
		j++;
		if (i == -1)
			return (ft_error());
		if (set->str[0] == '\0')
			break ;
		set = next_maillon(set);
	}
	if (j == 0)
		return (ft_error());
	set->next = NULL;
	return (next(origin, beggin, bloc));
}

int			main(int ac, char **av)
{
	t_sett	*set;
	t_sett	*origin;
	t_file	*beggin;
	t_bloc	*bloc;
	int		i;

	(void)av;
	i = 0;
	if (ac != 1)
		return (ft_error());
	bloc = (t_bloc *)malloc(sizeof(t_bloc));
	bloc->next = (t_bloc *)malloc(sizeof(t_bloc));
	beggin = (t_file *)malloc(sizeof(t_file));
	beggin->next = (t_file *)malloc(sizeof(t_file));
	g_link = (t_link *)malloc(sizeof(t_link));
	g_link->next = (t_link *)malloc(sizeof(t_link));
	set = (t_sett *)malloc(sizeof(t_sett));
	set->next = (t_sett *)malloc(sizeof(t_sett));
	origin = set;
	g_origin = g_link;
	set->next = (t_sett *)malloc(sizeof(t_sett));
	return (ft_cut_main(origin, beggin, bloc, set));
}
