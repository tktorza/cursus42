/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fdf_look.c                                         :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/22 14:22:49 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/22 16:22:10 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/fdf.h"

int		key_interact(int keycode, t_env *e)
{
	if (keycode == 53)
	{
		mlx_destroy_window(e->mlx, e->win);
		exit(0);
	}
	keycode == 47 ? e->pts = 1 : keycode;
	keycode == 22 ? e->pts = 0 : keycode;
	keycode == 69 ? e->height += 1 : keycode;
	keycode == 78 ? e->height -= 1 : keycode;
	keycode == 124 ? e->esc_right += 10 : keycode;
	keycode == 123 ? e->esc_right -= 10 : keycode;
	keycode == 125 ? e->esc_up += 10 : keycode;
	keycode == 126 ? e->esc_up -= 10 : keycode;
	keycode == 116 ? e->zoom += 1 : keycode;
	keycode == 1 ? e->start = 1 : keycode;
	keycode == 35 ? e->start = 0 : keycode;
	keycode == 121 && e->zoom > 5 ? e->zoom -= 1 : keycode;
	keycode == 49 ? e_initialize(e) : keycode;
	expose_hook(e);
	return (0);
}

void	ft_anim(t_env *e)
{
	if (e->start == 1)
	{
		if (e->height_going)
		{
			e->height += 0.1;
			if (e->height > 10)
				e->height_going = 0;
		}
		if (!e->height_going)
		{
			e->height -= 0.1;
			if (e->height < -10)
				e->height_going = 1;
		}
	}
}

int		look_put(void *mlx)
{
	t_env *e;

	e = mlx;
	ft_anim(e);
	expose_hook(e);
	return (1);
}
