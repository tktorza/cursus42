/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   doublon_del.c                                      :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:54:07 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:54:08 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

static int	same(char *s1, char *s2, int i)
{
	char	*c1;
	char	*c2;
	char	*d1;
	char	*d2;

	c1 = ft_strcut(s1, '-');
	c2 = ft_strcut(s2, '-');
	while (s1[i] && s1[i] != '-')
		i++;
	d1 = ft_strsub(s1, i + 1, ft_strlen(s1));
	i = 0;
	while (s2[i] && s2[i] != '-')
		i++;
	d2 = ft_strsub(s2, i + 1, ft_strlen(s1));
	if ((ft_strcmp(c1, d2) == 0 && ft_strcmp(c2, d1) == 0) \
			|| (ft_strcmp(c1, c2) == 0 && ft_strcmp(d2, d1) == 0))
		return (1);
	return (0);
}

static int	here(char *s, t_link *link)
{
	while (link->next)
	{
		if (link->str && same(s, link->str, 0) == 1)
			return (1);
		link = link->next;
	}
	return (0);
}

void		doublon_dell(void)
{
	t_link	*link;
	t_link	*origin;

	link = (t_link *)malloc(sizeof(t_link));
	link->str = g_origin->str;
	origin = link;
	link = link_next(link);
	g_origin = g_origin->next;
	while (g_origin->next)
	{
		if (g_origin->str && here(g_origin->str, origin) == 0)
		{
			link->str = g_origin->str;
			link = link_next(link);
		}
		g_origin = g_origin->next;
	}
	link->next = NULL;
	g_origin = origin;
}
