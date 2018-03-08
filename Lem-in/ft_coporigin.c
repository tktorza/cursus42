/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_coporigin.c                                     :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:54:26 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:54:27 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

t_link		*ft_cop_list(t_link *g_origin)
{
	t_link	*new;
	t_link	*origin_new;

	new = (t_link *)malloc(sizeof(t_link));
	origin_new = (t_link *)malloc(sizeof(t_link));
	g_link = g_origin;
	origin_new = new;
	while (g_link->next)
	{
		new->str = g_link->str;
		new = link_next(new);
		g_link = g_link->next;
	}
	new->next = NULL;
	free(new);
	new = NULL;
	return (origin_new);
}

t_sett		*ft_cop_listb(t_sett *origin)
{
	t_sett	*new;
	t_sett	*origin_new;

	new = (t_sett *)malloc(sizeof(t_sett));
	origin_new = new;
	while (origin->next)
	{
		new->str = origin->str;
		new = next_maillon(new);
		origin = origin->next;
	}
	new->next = NULL;
	free(new);
	new = NULL;
	return (origin_new);
}

t_aff		*new_aff(t_aff *tmp, t_bloc *bloc, int *nb)
{
	t_aff	*origin;

	origin = tmp;
	g_bloc = bloc;
	while (bloc->next)
	{
		if (bloc->ant_nb != 0)
		{
			*nb += 1;
			tmp->index = *nb;
			tmp->bloc = bloc->str;
			tmp->room = room_next(g_start, bloc->str);
			tmp = aff_next(tmp);
			bloc->ant_nb -= 1;
		}
		bloc = bloc->next;
	}
	tmp->next = NULL;
	return (origin);
}

t_aff		*actual_aff(t_aff *origin, t_aff *new)
{
	t_aff	*origini;

	if (origin->index == new->index)
		return (new->next);
	origini = origin;
	while (origin->next && (origin->next)->index != new->index)
		origin = origin->next;
	origin->next = (origin->next)->next;
	return (origini);
}

t_aff		*delete_end(t_aff *new)
{
	t_aff	*origin;

	while (new && new->final == 10)
		new = new->next;
	origin = new;
	while (new->next && (new->next)->next)
	{
		if ((new->next)->final == 10)
			new->next = (new->next)->next;
		new = new->next;
	}
	return (origin);
}
