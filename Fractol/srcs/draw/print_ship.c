/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   print_ship.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:59:29 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 16:38:55 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/fractol.h"

void				ship_thread(t_env *e, int mod)
{
	if (mod == 1)
	{
		e->calc->oldre = fabs(e->calc->newre);
		e->calc->oldim = fabs(e->calc->newim);
		e->calc->newre = e->calc->oldre * e->calc->oldre
		- e->calc->oldim * e->calc->oldim + e->real;
		e->calc->newim = 2 * e->calc->oldre * e->calc->oldim + e->imnbr;
	}
	mod == 2 ? (e->real = 1.5 * (e->point->x - WIDTH / 2)
	/ (0.5 * e->zoom * WIDTH) + e->move_x) : mod;
	mod == 2 ? (e->imnbr = (e->point->y - HEIGHT / 2)
	/ (0.5 * e->zoom * HEIGHT) + e->move_y) : mod;
	mod != 1 ? (e->calc->newre = 0) : mod;
	mod != 1 ? (e->calc->newim = 0) : mod;
	mod != 1 ? (e->calc->oldre = 0) : mod;
	mod != 1 ? (e->calc->oldim = 0) : mod;
}

void				calc_ship(int *i, t_env *e)
{
	while (*i < e->iter)
	{
		ship_thread(e, 1);
		if (e->calc->newre * e->calc->newre +
			e->calc->newim * e->calc->newim > 4)
			break ;
		(*i)++;
	}
}

void				ship_draw_1(void *list)
{
	t_color			color;
	t_env			*e;
	int				i;
	float			c;

	c = 0.0;
	e = list;
	e->point->y = -1;
	while (++(e->point->y) < 800)
	{
		e->point->x = -1;
		while (++(e->point->x) < WIDTH)
		{
			ship_thread(e, 2);
			i = 0;
			calc_ship(&i, e);
			color = get_color(color, e, i, c);
			draw_pixel(e, e->point->x, e->point->y, color);
		}
	}
}
