/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   generation.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:55:21 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:55:22 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

static t_link	*cut(t_link *tmp, t_link *path)
{
	tmp->str = path->str;
	tmp = link_next(tmp);
	return (tmp);
}

static t_link	*cut2(t_link *own, t_link *path)
{
	while (own->next && ft_check_all(path->str, own->str) != 0)
		own = own->next;
	return (own);
}

t_link			*generat(t_link *path, t_link *own, t_link *curent, t_link *tmp)
{
	t_link		*tmp_origin;
	t_link		*own_origin;

	own_origin = own;
	tmp_origin = tmp;
	while (path->next)
	{
		own = curent;
		if (check_end2(path->str) == 1)
			tmp = cut(tmp, path);
		while (current_possibility(path->str, own))
		{
			own = own_origin;
			own = cut2(own, path);
			if (own->next && check_end2(path->str) == 0)
			{
				tmp->str = ft_copy(path->str, own->str);
				own_origin = link_dell(own_origin, own->str);
				tmp = link_next(tmp);
			}
		}
		path = path->next;
	}
	tmp->next = NULL;
	return (tmp_origin);
}

t_link			*generation(t_link *path, t_link *curent)
{
	t_link		*tmp;
	t_link		*own;

	tmp = (t_link *)malloc(sizeof(t_link));
	own = curent;
	return (generat(path, own, curent, tmp));
}
