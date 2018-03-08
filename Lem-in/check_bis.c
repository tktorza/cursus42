/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   check_bis.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:53:46 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 16:26:57 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

int				check(t_sett *origin)
{
	t_sett		*set;
	int			i;

	i = 0;
	set = origin;
	if (set == NULL)
		return (-1);
	if (set->str[i] == '\0' || set->str[0] == '\n')
		return (-1);
	while (set->str[i] != '\0')
	{
		if (set->str[i] < 49 || set->str[i] > 57)
			return (-1);
		i++;
	}
	set = set->next;
	return (ft_cut_check(set, 0, 0, 0));
}

static int		ft_check_end(int j, int k)
{
	if (j != 2)
		return (-1);
	if (k == 0)
		return (-1);
	return (0);
}

int				ft_cut_check(t_sett *set, int i, int j, int k)
{
	while (set->next)
	{
		i = -1;
		while (set->str[++i] != '\0')
		{
			if (ft_strcmp("##start", set->str) == 0 ||
				ft_strcmp("##end", set->str) == 0)
				j++;
			if (set->str[0] == '#')
				break ;
			if (set->str[i] == ' ' || set->str[0] == 'L')
			{
				i++;
				while (set->str[i] >= 48 && set->str[i] <= 57)
					i++;
				if ((set->str[i] != ' ' && (set->str[i] < 48
					|| set->str[i] > 57)) || set->str[0] == 'L')
					return (-1);
			}
			if (set->str[i] == '-')
				k++;
		}
		set = set->next;
	}
	return (ft_check_end(j, k));
}

void			ft_affich2(t_sett *beggin)
{
	while (beggin->next)
	{
		ft_printf("%s\n", beggin->str);
		beggin = beggin->next;
	}
}
