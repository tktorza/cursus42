/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_parsing.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:54:43 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:54:44 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

t_file		*file_start(t_sett *thing, t_file *file)
{
	int		nb;

	nb = ft_atoi(thing->str);
	while (thing->next)
	{
		if (ft_strstr(thing->str, "##start"))
		{
			thing = thing->next;
			file->name = ft_strcut(thing->str, ' ');
			file->ant = nb;
			g_ant = nb;
			file->x = file_coor(thing->str, 'x');
			file->y = file_coor(thing->str, 'y');
			g_start = (char *)malloc(sizeof(char) * ft_strlen(file->name));
			g_start = file->name;
			return (file_next(file));
		}
		thing = thing->next;
	}
	return (NULL);
}

t_file		*file_end(t_sett *thing, t_file *file)
{
	while (thing->next)
	{
		if (ft_strstr(thing->str, "##end"))
		{
			thing = thing->next;
			file->name = ft_strcut(thing->str, ' ');
			file->ant = 0;
			file->x = file_coor(thing->str, 'x');
			file->y = file_coor(thing->str, 'y');
			g_end = (char *)malloc(sizeof(char) * ft_strlen(file->name));
			g_end = file->name;
			return (file_next(file));
		}
		thing = thing->next;
	}
	return (NULL);
}

int			file_name(char *s)
{
	int i;

	i = 0;
	while (s[i])
	{
		if (s[i] == ' ')
			return (i);
		i++;
	}
	return (0);
}

t_file		*put_file(t_sett *thing, t_file *file)
{
	file->ant = 0;
	file->x = file_coor(thing->str, 'x');
	file->y = file_coor(thing->str, 'y');
	file->name = ft_strsub(thing->str, 0, file_name(thing->str));
	return (file_next(file));
}

t_file		*ft_parsing(t_sett *thing)
{
	t_file	*file;
	t_file	*origin;

	file = (t_file *)malloc(sizeof(t_file));
	origin = file;
	file = file_start(thing, file);
	file = file_end(thing, file);
	thing = set_clean(thing);
	thing = thing->next;
	while (thing->next)
	{
		if (ft_strchr(thing->str, '-'))
		{
			g_link->str = thing->str;
			g_link = link_next(g_link);
		}
		else if (!ft_strstr(thing->str, "#"))
			file = put_file(thing, file);
		thing = thing->next;
	}
	free(file);
	file = NULL;
	if (ft_origin_check(origin) == 1)
		g_origin = NULL;
	return (origin);
}
