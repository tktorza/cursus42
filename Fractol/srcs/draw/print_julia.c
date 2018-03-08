/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   print_julia.c                                      :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:59:01 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 15:59:04 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/fractol.h"

void			julia_thread(t_env *e, int mod)
{
	if (mod == 1)
	{
		e->calc->oldre = e->calc->newre;
		e->calc->oldim = e->calc->newim;
		e->calc->newre = e->calc->oldre * e->calc->oldre - e->calc->oldim *
		e->calc->oldim + e->real;
		e->calc->newim = 2 * e->calc->oldre * e->calc->oldim + e->imnbr;
	}
	(mod == 2) ? (e->calc->newre = 1.5 * (e->point->x - WIDTH / 2) /
	(0.5 * e->zoom * WIDTH) + e->move_x) : mod;
	(mod == 2) ? (e->calc->newim = (e->point->y - HEIGHT / 2) /
	(0.5 * e->zoom * HEIGHT) + e->move_y) : mod;
	(mod == 3) ? (e->calc->newre = 1.5 * (e->points->x - WIDTH / 2) /
	(0.5 * e->zoom * WIDTH) + e->move_x) : mod;
	(mod == 3) ? (e->calc->newim = (e->points->y - HEIGHT / 2) /
	(0.5 * e->zoom * HEIGHT) + e->move_y) : mod;
	(mod == 4) ? (e->calc->newre = 1.5 * (e->pointa->x - WIDTH / 2)
	/ (0.5 * e->zoom * WIDTH) + e->move_x) : mod;
	(mod == 4) ? (e->calc->newim = (e->pointa->y - HEIGHT / 2) /
	(0.5 * e->zoom * HEIGHT) + e->move_y) : mod;
	(mod == 5) ? (e->calc->newre = 1.5 * (e->pointb->x - WIDTH / 2) /
	(0.5 * e->zoom * WIDTH) + e->move_x) : mod;
	(mod == 5) ? (e->calc->newim = (e->pointb->y - HEIGHT / 2) /
	(0.5 * e->zoom * HEIGHT) + e->move_y) : mod;
}

void			*draw_1(void *list)
{
	t_env		*e;
	t_color		color;
	int			i;
	float		c;

	c = 0.0;
	e = (t_env*)list;
	e->point->y = 0;
	while (++(e->point->y) < 200)
	{
		e->point->x = -1;
		while (++(e->point->x) < WIDTH)
		{
			julia_thread(e, 2);
			i = -1;
			while (++i < e->iter && (e->calc->newre * e->calc->newre +
				e->calc->newim * e->calc->newim) < 4)
				julia_thread(e, 1);
			color = get_color(color, e, i, c);
			draw_pixel(e, e->point->x, e->point->y, color);
		}
	}
	return (NULL);
}

void			*draw_2(void *list)
{
	t_env		*e;
	t_color		color;
	int			i;
	float		c;

	c = 0.0;
	e = (t_env*)list;
	e->points->y = 199;
	while (++(e->points->y) < 400)
	{
		e->points->x = -1;
		while (++(e->points->x) < WIDTH)
		{
			julia_thread(e, 3);
			i = -1;
			while (++i < e->iter && (e->calc->newre * e->calc->newre +
				e->calc->newim * e->calc->newim) < 4)
				julia_thread(e, 1);
			color = get_color(color, e, i, c);
			draw_pixel(e, e->points->x, e->points->y, color);
		}
	}
	return (NULL);
}

void			*draw_3(void *list)
{
	t_env		*e;
	t_color		color;
	int			i;
	float		c;

	c = 0.0;
	e = (t_env*)list;
	e->pointa->y = 399;
	while (++(e->pointa->y) < 600)
	{
		e->pointa->x = 0;
		while (e->pointa->x < WIDTH)
		{
			julia_thread(e, 4);
			i = -1;
			while (++i < e->iter && (e->calc->newre * e->calc->newre +
				e->calc->newim * e->calc->newim) < 4)
				julia_thread(e, 1);
			color = get_color(color, e, i, c);
			draw_pixel(e, e->pointa->x, e->pointa->y, color);
			e->pointa->x++;
		}
	}
	return (NULL);
}

void			*draw_4(void *list)
{
	t_env		*e;
	t_color		color;
	int			i;
	float		c;

	c = 0.0;
	e = (t_env*)list;
	e->pointb->y = 599;
	while (++(e->pointb->y) < 800)
	{
		e->pointb->x = 0;
		while (e->pointb->x < WIDTH)
		{
			julia_thread(e, 5);
			i = -1;
			while (++i < e->iter && (e->calc->newre * e->calc->newre +
				e->calc->newim * e->calc->newim) < 4)
				julia_thread(e, 1);
			color = get_color(color, e, i, c);
			draw_pixel(e, e->pointb->x, e->pointb->y, color);
			e->pointb->x++;
		}
	}
	return (NULL);
}
