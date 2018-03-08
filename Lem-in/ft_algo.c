/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_algo.c                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:54:18 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:54:19 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

t_bloc			*bloc_conv(t_bloc *bloc, t_link *final)
{
	t_bloc		*origin;

	origin = bloc;
	while (final->next)
	{
		bloc->str = final->str;
		bloc->nb = ft_linklen(final->str);
		bloc->ant_nb = 0;
		bloc = bloc_next(bloc);
		final = final->next;
	}
	bloc->next = NULL;
	return (origin);
}

t_bloc			*ft_total(int *total, t_bloc *bloc)
{
	int			compare;
	t_bloc		*origin;

	origin = bloc;
	compare = 0;
	while (bloc->next)
	{
		compare += bloc->nb;
		if (compare >= g_ant)
		{
			bloc = bloc_next(bloc);
			bloc->next = NULL;
			break ;
		}
		bloc = bloc->next;
	}
	*total = g_ant + compare;
	return (origin);
}

void			start_values(t_bloc *bloc, t_tools *tool)
{
	tool->path = 0;
	while (bloc->next)
	{
		tool->path += 1;
		bloc = bloc->next;
	}
	tool->rest = tool->total % tool->path;
	tool->div = tool->total / tool->path;
}

t_bloc			*ant_nb(t_bloc *bloc, t_tools tool)
{
	t_bloc		*origin;

	origin = bloc;
	while (bloc->next)
	{
		bloc->ant_nb = tool.div - bloc->nb + tool.rest;
		tool.rest = 0;
		bloc = bloc->next;
	}
	return (origin);
}

t_bloc			*ft_algo(t_link *final)
{
	t_bloc		*bloc;
	t_tools		tool;

	bloc = (t_bloc *)malloc(sizeof(t_bloc));
	bloc->next = (t_bloc *)malloc(sizeof(t_bloc));
	bloc = ft_total(&tool.total, bloc_conv(bloc, final));
	start_values(bloc, &tool);
	bloc = ant_nb(bloc, tool);
	return (bloc);
}
