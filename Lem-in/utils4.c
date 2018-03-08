/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   utils4.c                                           :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:56:52 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:56:53 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

int			current_possibility(char *str, t_link *own)
{
	while (own->next)
	{
		if (ft_check_all(str, own->str) == 0 && check_end2(str) == 0)
			return (1);
		own = own->next;
	}
	return (0);
}

int			ft_check_all(char *str, char *tmp)
{
	if (ft_check1(str, tmp) == 0 || ft_check2(tmp, str) == 0)
		return (0);
	return (-1);
}

int			link_compare(char *str, char *tmp)
{
	char	*s;
	int		j;
	int		i;

	j = 0;
	i = (int)ft_strlen(str);
	while (str[i] != '-')
		i--;
	i--	;
	while (str[i] != '-' || i == 0)
		i--;
	i++;
	s = half_link(str, i);
	if (ft_strcmp(s, tmp) == 0 || ft_strcmp(ft_linkrev(s), tmp) == 0)\
	{
		free(s);
		s = NULL;
		return (0);
	}
	return (1);
}

int			general_possibility(t_link *start, t_link *path, t_link *curent)
{
	t_link	*origin;

	origin = path;
	if (!path)
	{
		path->str = start->str;
		path = link_next(path);
		path->next = NULL;
		path = origin;
	}
	while (path->next)
	{
		if (current_possibility(path->str, curent))
			return (1);
		path = path->next;
	}
	return (0);
}

t_iter		*free_iter(t_iter *iter)
{
	free(iter);
	iter = NULL;
	return (iter);
}
