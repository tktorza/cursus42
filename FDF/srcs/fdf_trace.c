/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fdf_trace.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/16 13:27:33 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/22 16:22:24 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/fdf.h"

void	fdf_trace_right(t_point ok, t_env *e, t_line *line)
{
	t_point	a;
	t_point	b;

	a.x = conv_x(ok.x, ok.y, e);
	a.y = conv_y(ok.x, ok.y, line->line[ok.x], e);
	b.x = conv_x(ok.x + 1, ok.y, e);
	b.y = conv_y(ok.x + 1, ok.y, line->line[ok.x + 1], e);
	e->zed = line->line[ok.x + 1];
	print_line(a, b, e);
	print_line(a, b, e);
}

void	fdf_trace_down(t_point ok, t_env *e, t_line *line)
{
	t_point	a;
	t_point	b;

	a.x = conv_x(ok.x, ok.y, e);
	a.y = conv_y(ok.x, ok.y, line->line[ok.x], e);
	b.x = conv_x(ok.x, ok.y + 1, e);
	b.y = conv_y(ok.x, ok.y + 1, (line->next)->line[ok.x], e);
	e->zed = (line->next)->line[ok.x];
	print_line(a, b, e);
}

t_var	init_var(t_point p1, t_point p2)
{
	t_var	var;

	var.dx = abs((int)p2.x - (int)p1.x);
	var.sx = p1.x < p2.x ? 1 : -1;
	var.dy = abs((int)p2.y - (int)p1.y);
	var.sy = p1.y < p2.y ? 1 : -1;
	var.error = (var.dx > var.dy ? var.dx : -var.dy) / 2;
	var.x = p1.x;
	var.y = p1.y;
	return (var);
}

void	print_line(t_point a, t_point b, t_env *env)
{
	t_var	var;
	t_point	p;

	var = init_var(a, b);
	while (1)
	{
		p.x = var.x;
		p.y = var.y;
		if (outside_window(env, p.x, p.y))
			draw_pixel(env, p.x, p.y, (t_color) {env->c1, env->c2, env->c3});
		if (var.x == b.x && var.y == b.y)
			break ;
		var.e2 = var.error;
		if (var.e2 > -var.dx)
		{
			var.error -= var.dy;
			var.x += var.sx;
		}
		if (var.e2 < var.dy)
		{
			var.error += var.dx;
			var.y += var.sy;
		}
	}
}
