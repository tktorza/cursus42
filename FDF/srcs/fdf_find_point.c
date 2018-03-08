/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fdf_find_point.c                                   :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/15 10:31:36 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/22 16:21:54 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/fdf.h"

int			outside_window(t_env *e, int x, int y)
{
	if (!(x > e->large - 1 || x < 0 || y > e->longer - 1 || y < 0))
		return (1);
	else
		return (0);
}

static void	draw_next(int x, int y, t_line *line, t_env *e)
{
	if (e->onetwo == 1)
	{
		draw_pixel(e, conv_x(x, y, e), conv_y(x, y, line->line[x], e), \
				(t_color) {204, 0, 0});
		e->onetwo = 0;
	}
	else
	{
		e->onetwo = 1;
		draw_pixel(e, conv_x(x, y, e), conv_y(x, y, line->line[x], e), \
				(t_color) {192, 192, 192});
	}
}

void		fdf_draw(t_line *line, t_env *e)
{
	int		x;
	int		y;
	t_line	*origine;

	origine = (t_line *)malloc(sizeof(t_line));
	origine = line;
	y = 0;
	if (e->pts == 1)
	{
		while (line->next)
		{
			x = 0;
			while (x < line->max)
			{
				if (outside_window(e, conv_x(x, y, e), \
							conv_y(x, y, line->line[x], e)))
					draw_next(x, y, line, e);
				x++;
			}
			y++;
			line = line->next;
		}
	}
	if (e->pts == 0)
		fdf_segment(origine, e);
}

void		fdf_segment(t_line *line, t_env *e)
{
	int		x;
	int		y;
	t_point	a;

	y = 0;
	while (line->next)
	{
		x = 0;
		while (x < line->max)
		{
			a.x = x;
			a.y = y;
			if (x < line->max - 1)
				fdf_trace_right(a, e, line);
			if ((line->next)->next && x < (line->next)->max)
				fdf_trace_down(a, e, line);
			x++;
		}
		y++;
		line = line->next;
	}
}
