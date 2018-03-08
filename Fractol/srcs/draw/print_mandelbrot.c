/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   print_mandelbrot.c                                 :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:59:16 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 15:59:19 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/fractol.h"

void				mandel_thread(t_env *e, int mod)
{
	if (mod == 1)
	{
		e->calc->oldre = e->calc->newre;
		e->calc->oldim = e->calc->newim;
		e->calc->newre = e->calc->oldre * e->calc->oldre
		- e->calc->oldim * e->calc->oldim + e->real;
		e->calc->newim = 2 * e->calc->oldre * e->calc->oldim + e->imnbr;
	}
	mod == 2 ? (e->real = 1.5 * (e->point->x - WIDTH / 2)
	/ (0.5 * e->zoom * WIDTH) + e->move_x) : mod;
	mod == 2 ? (e->imnbr = (e->point->y - HEIGHT / 2)
	/ (0.5 * e->zoom * HEIGHT) + e->move_y) : mod;
	mod == 3 ? (e->real = 1.5 * (e->points->x - WIDTH / 2)
	/ (0.5 * e->zoom * WIDTH) + e->move_x) : mod;
	mod == 3 ? (e->imnbr = (e->points->y - HEIGHT / 2)
	/ (0.5 * e->zoom * HEIGHT) + e->move_y) : mod;
	mod == 4 ? (e->real = 1.5 * (e->pointa->x - WIDTH / 2)
	/ (0.5 * e->zoom * WIDTH) + e->move_x) : mod;
	mod == 4 ? (e->imnbr = (e->pointa->y - HEIGHT / 2)
	/ (0.5 * e->zoom * HEIGHT) + e->move_y) : mod;
	mod != 1 ? (e->calc->newre = 0) : mod;
	mod != 1 ? (e->calc->newim = 0) : mod;
	mod != 1 ? (e->calc->oldre = 0) : mod;
	mod != 1 ? (e->calc->oldim = 0) : mod;
}

void				calc_mandel(int *i, t_env *e)
{
	while (*i < e->iter)
	{
		mandel_thread(e, 1);
		if (e->calc->newre * e->calc->newre +
			e->calc->newim * e->calc->newim > 4)
			break ;
		(*i)++;
	}
}

void				*mandel_draw_1(void *list)
{
	t_color			color;
	t_env			*e;
	int				i;
	float			c;

	c = 0.0;
	e = list;
	e->point->y = -1;
	while (++(e->point->y) < 265)
	{
		e->point->x = -1;
		while (++(e->point->x) < WIDTH)
		{
			mandel_thread(e, 2);
			i = 0;
			calc_mandel(&i, e);
			color = get_color(color, e, i, c);
			draw_pixel(e, e->point->x, e->point->y, color);
		}
	}
	return (NULL);
}

void				*mandel_draw_2(void *list)
{
	t_color			color;
	t_env			*e;
	int				i;
	float			c;

	c = 0.0;
	e = list;
	e->points->y = 264;
	while (++(e->points->y) < 530)
	{
		e->points->x = -1;
		while (++(e->points->x) < WIDTH)
		{
			mandel_thread(e, 3);
			i = 0;
			calc_mandel(&i, e);
			color = get_color(color, e, i, c);
			draw_pixel(e, e->points->x, e->points->y, color);
		}
	}
	return (NULL);
}

void				*mandel_draw_3(void *list)
{
	t_color			color;
	t_env			*e;
	int				i;
	float			c;

	c = 0.0;
	e = list;
	e->pointa->y = 529;
	while (++(e->pointa->y) < 800)
	{
		e->pointa->x = -1;
		while (++(e->pointa->x) < WIDTH)
		{
			mandel_thread(e, 4);
			i = 0;
			calc_mandel(&i, e);
			color = get_color(color, e, i, c);
			draw_pixel(e, e->pointa->x, e->pointa->y, color);
		}
	}
	return (NULL);
}
