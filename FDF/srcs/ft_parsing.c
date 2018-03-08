/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_parsing.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/14 14:11:56 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/22 17:41:17 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/fdf.h"

static t_line	*next_line(t_line *line)
{
	t_line	*new;

	new = (t_line *)malloc(sizeof(t_line));
	line->next = (t_line *)malloc(sizeof(t_line));
	if (new == NULL || line->next == NULL)
		return (NULL);
	line->next = new;
	return (new);
}

static void		bloubls(char **tab, t_env *e, t_line *line)
{
	while (tab[e->i])
	{
		line->line[e->x] = ft_atoi(tab[e->i]);
		line->line[e->x] > e->max ? e->max = line->line[e->x] : e->max;
		line->line[e->x] < e->min ? e->min = line->line[e->x] : e->min;
		e->i += 1;
		e->x += 1;
	}
}

static t_line	*bis_line(int fd, t_env *e, t_line *line, char **tab)
{
	while (get_next_line(fd, &e->str) > 0)
	{
		e->i = 0;
		tab = ft_split(ft_strdup(e->str));
		while (tab[e->i])
			e->i += 1;
		line->line = (int *)malloc(sizeof(int) * (e->i + 2));
		line->max = e->i;
		line->max > e->xmax ? e->xmax = line->max : e->xmax;
		e->i = 0;
		e->x = 0;
		bloubls(tab, e, line);
		line = next_line(line);
		e->ymax += 1;
		free(e->str);
	}
	return (line);
}

static t_line	*line_sort(char **argv, t_env *e)
{
	int		fd;
	char	**tab;
	t_line	*line;
	t_line	*origin;

	tab = NULL;
	origin = (t_line *)malloc(sizeof(t_line));
	line = (t_line *)malloc(sizeof(t_line));
	origin = line;
	fd = open(argv[1], O_RDONLY);
	if (fd == -1)
		return (NULL);
	line = bis_line(fd, e, line, tab);
	line->next = NULL;
	return (origin);
}

int				main(int argc, char **argv)
{
	t_env	e;

	if (argc == 2)
	{
		e.xmax = 0;
		e.ymax = 0;
		e.min = 0;
		e.max = 0;
		e.start = 0;
		e.height_going = 1;
		e.line = line_sort(argv, &e);
		if (e.line == NULL)
			return (fdf_error());
		e.mlx = mlx_init();
		e_initialize(&e);
		e.win = mlx_new_window(e.mlx, e.large, e.longer, "mlx 42");
		mlx_expose_hook(e.win, expose_hook, &e);
		mlx_loop_hook(e.mlx, look_put, &e);
		mlx_hook(e.win, 2, 3, key_interact, &e);
		mlx_loop(e.mlx);
	}
	return (0);
}
