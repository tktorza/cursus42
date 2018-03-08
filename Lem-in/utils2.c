/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   utils2.c                                           :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:56:32 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:56:33 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

t_link			*short_final(t_link *final)
{
	t_link		*tmp;
	t_link		*last;
	t_link		*begin;

	tmp = NULL;
	last = NULL;
	begin = final;
	return (short_fin(final, tmp, last, begin));
}

t_link			*short_fin(t_link *fin, t_link *tmp, t_link *last, t_link *beg)
{
	while (fin->next && fin->next->next)
	{
		if (ft_linklen(fin->str) > ft_linklen(fin->next->str)
			&& fin->next->next)
		{
			tmp = fin->next;
			if (beg == fin)
				beg = tmp;
			if (last)
				last->next = tmp;
			fin->next = tmp->next;
			tmp->next = fin;
			fin = beg;
		}
		else
		{
			last = fin;
			fin = fin->next;
		}
	}
	return (beg);
}

char			*ft_strcut(char *str, char c)
{
	int			tall;

	tall = 0;
	while (str[tall] && str[tall] != c)
		tall++;
	return (ft_strsub(str, 0, tall));
}

int				file_coor(char *str, char c)
{
	int			i;
	int			val;

	if (c == 'x')
		val = 1;
	else
		val = 2;
	i = 0;
	while (str[i])
	{
		if (str[i] == ' ')
		{
			val--;
			i++;
			if (val == 0)
				return (ft_atoi(ft_strsub(str, i, ft_strlen(str))));
		}
		i++;
	}
	return (-1);
}

t_sett			*set_clean(t_sett *thing)
{
	t_sett		*origin;

	origin = thing;
	while (thing->next)
	{
		if ((thing->next)->next && (ft_strstr((thing->next)->str, "##start")
			|| ft_strstr((thing->next)->str, "##end")))
			thing->next = ((thing->next)->next)->next;
		thing = thing->next;
	}
	return (origin);
}
