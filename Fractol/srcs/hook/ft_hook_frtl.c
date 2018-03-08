/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_hook_frtl.c                                     :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:56:23 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 16:16:24 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/fractol.h"

void		color_change(int keycode, t_env *e)
{
	if (keycode == 84)
		e->ajust -= 1;
	if (keycode == 83)
		e->ajust += 1;
}

void		zoom_value(int keycode, t_env *e)
{
	if (keycode == 69 && (e->fractal == 1 || e->fractal == 3))
	{
		e->imnbr += 0.001 / e->zoom;
		e->real += 0.001 / e->zoom;
	}
	if (keycode == 78 && (e->fractal == 1 || e->fractal == 3))
	{
		e->imnbr -= 0.001 / e->zoom;
		e->real -= 0.001 / e->zoom;
	}
	if (keycode == 69 && (e->fractal == 2 || e->fractal == 4))
		e->iter += 5;
	if (keycode == 78 && (e->fractal == 2 || e->fractal == 4))
		e->iter -= 5;
	if (keycode == 116)
	{
		e->zoom = e->zoom * 1.2;
		e->iter += 5;
	}
	if (keycode == 121)
	{
		e->zoom = e->zoom / 1.2;
		e->iter -= 5;
	}
}

void		move_change(int keycode, t_env *e)
{
	if (keycode == 123)
		e->move_x += 0.1 / e->zoom;
	if (keycode == 124)
		e->move_x -= 0.1 / e->zoom;
	if (keycode == 126)
		e->move_y += 0.1 / e->zoom;
	if (keycode == 125)
		e->move_y -= 0.1 / e->zoom;
}

void		spe_frl(int keycode, t_env *e)
{
	if (keycode == 49 && (e->fractal == 1 || e->fractal == 3))
	{
		e->imnbr = 0.27015;
		e->real = -0.7;
		e->zoom = 1;
		e->iter = 50;
		e->move_x = 0;
		e->move_y = 0;
		e->ajust = 0;
	}
	if (keycode == 49 && (e->fractal == 2 || e->fractal == 4))
	{
		e->zoom = 0.694444;
		e->iter = 50;
		e->move_x = -0.5;
		e->move_y = 0;
		e->ajust = 0;
	}
}

int			key_hook(int keycode, t_env *e)
{
	if (keycode == 53)
	{
		mlx_destroy_window(e->mlx, e->win);
		exit(0);
	}
	spe_frl(keycode, e);
	zoom_value(keycode, e);
	color_change(keycode, e);
	move_change(keycode, e);
	if (keycode == 119 && e->mouse_event)
		e->mouse_event = 0;
	else if (keycode == 119 && !e->mouse_event)
		e->mouse_event = 1;
	expose_hook(e);
	return (0);
}
