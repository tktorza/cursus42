/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_path.c                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:54:53 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:54:55 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

void			ft_freeall(t_link *b_curent, t_link *curent, t_link *path)
{
	free(b_curent);
	b_curent = NULL;
	free(curent);
	curent = NULL;
	free(path);
	path = NULL;
}

t_bloc			*ft_calling(void)
{
	t_link		*new;
	t_link		*tmp;

	new = (t_link *)malloc(sizeof(t_link));
	g_link = g_origin;
	tmp = new;
	while (check_start(g_origin->str) == 1)
	{
		if (check_start2(g_origin->str) == 1)
			new->str = ft_linkrev(g_origin->str);
		else
			new->str = g_origin->str;
		new = link_next(new);
		g_origin = g_origin->next;
	}
	new->next = NULL;
	return (ft_path(tmp));
}

t_bloc			*ft_path(t_link *start)
{
	t_link		*path;
	t_link		*curent;
	t_link		*final;

	path = (t_link *)malloc(sizeof(t_link));
	curent = (t_link *)malloc(sizeof(t_link));
	final = (t_link *)malloc(sizeof(t_link));
	return (ft_cut_path(start, curent, final, path));
}

t_bloc			*ft_cut_path(t_link *start, t_link *curent,
	t_link *final, t_link *path)
{
	t_link		*b_curent;
	t_link		*origin;

	origin = path;
	b_curent = ft_cop_list(g_origin);
	while (start->next)
	{
		curent = g_origin;
		path->str = start->str;
		path = link_next(path);
		path->next = NULL;
		path = origin;
		while (general_possibility(start, path, curent))
		{
			path = generation(path, curent);
			curent = dell_occurrence(path, curent);
		}
		final = path_selection(final, path);
		origin = path;
		g_origin = ft_cop_list(b_curent);
		start = start->next;
	}
	ft_freeall(b_curent, curent, path);
	return (next_path(final));
}

t_bloc			*next_path(t_link *final)
{
	if (final->str == NULL)
		return (NULL);
	final = short_final(final);
	ft_affich2(g_sett_origin);
	ft_printf("There are many paths :\n-------------------\n");
	ft_affich(final, NULL);
	ft_printf("------------------\n");
	final = ft_selection(ft_cop_list(final), final);
	ft_printf("But only need this paths :\n-----------------\n");
	ft_affich(final, NULL);
	ft_printf("------------------\n");
	return (ft_algo(final));
}
