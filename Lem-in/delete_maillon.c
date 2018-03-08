/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   delete_maillon.c                                   :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:53:59 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:54:00 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

t_link		*link_dell(t_link *tmp, char *str)
{
	t_link	*origin;

	origin = (t_link *)malloc(sizeof(t_link));
	origin = tmp;
	if (ft_strcmp(tmp->str, str) == 0)
		origin = tmp->next;
	tmp = origin;
	while (tmp->next && tmp->next->next)
	{
		if (ft_strcmp((tmp->next)->str, str) == 0)
			tmp->next = (tmp->next)->next;
		tmp = tmp->next;
	}
	return (origin);
}

t_link		*dell_occurrence(t_link *path, t_link *curent)
{
	t_link	*curent_origin;

	curent_origin = (t_link *)malloc(sizeof(t_link));
	curent_origin = curent;
	while (path->next)
	{
		curent = curent_origin;
		while (curent && curent->next && link_compare(path->str, curent->str))
			curent = curent->next;
		if (curent && curent->str)
			curent_origin = link_dell(curent, curent->str);
		path = path->next;
	}
	return (curent_origin);
}

t_link		*path_selection(t_link *final, t_link *path)
{
	t_link	*begin;

	begin = final;
	while (final->next)
		final = final->next;
	while (path->next)
	{
		if (check_end2(path->str) == 1)
		{
			final->str = path->str;
			final = link_next(final);
		}
		path = path->next;
	}
	final->next = NULL;
	return (begin);
}
