/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fdf_look.c                                         :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/22 14:22:49 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/01 03:50:09 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "Includes/fractol.h"

int		expose_hook(t_env *e)
{

	e->image = mlx_new_image(e->mlx, e->large, e->longer);
	if (e->frac == 'J')
		multi_thread(e);
	else if (e->frac == 'M')
		mandel_thread(e);
	else if (e->frac == 'F')
		other_thread(e);
	mlx_put_image_to_window(e->mlx, e->win, e->image, 0, 0);
	mlx_destroy_image(e->mlx, e->image);
	return (0);
}

int		key_interact(int keycode, t_env *e)
{
	if (keycode == 53)
	{
		mlx_destroy_window(e->mlx, e->win);
		exit(0);
	}
//	printf("keycode = %d\n", keycode);
/*	keycode == 69 ? e->height += 1 : keycode;
	keycode == 78 ? e->height -= 1 : keycode;
*/	keycode == 124 ? e->esc_right += 10 : keycode;
	keycode == 8 ? e->c1 += 0.1 : keycode;
	keycode == 9 ? e->c2 += 0.1 : keycode;
	keycode == 11 ? e->c1 -= 0.1 : keycode;
	keycode == 7 ? e->c2 -= 0.1 : keycode;
	keycode == 123 ? e->esc_right -= 10 : keycode;
	keycode == 125 ? e->esc_up += 10 : keycode;
	keycode == 126 ? e->esc_up -= 10 : keycode;
	keycode == 24 ? e->it_max += 1 : keycode;
/*	keycode == 1 ? e->start = 1 : keycode;
	keycode == 35 ? e->start = 0 : keycode;
*/	keycode == 27 ? e->zoom -= 10 : keycode;
	keycode == 24 ? e->zoom += 10 : keycode;
	keycode == 49 ? e_initialize(e) : keycode;
	look_put(e);
	return (0);
}

int		mouse_interact(int keycode, t_env *e)
{
	keycode == 1 ? e->zoom += 5 : keycode;
	look_put(e);
	return (0);
}

/*void	ft_anim(t_env *e)
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
*/
int		look_put(void *mlx)
{
	t_env *e;

	e = mlx;
	expose_hook(e);
	return (1);
}
