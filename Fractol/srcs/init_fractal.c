/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   init_fractal.c                                     :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:56:10 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 16:18:21 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/fractol.h"

void		init_fractol(t_env *e)
{
	if (strcmp(e->argv, "julia") == 0 || strcmp(e->argv, "julia2") == 0)
	{
		e->imnbr = 0.27015;
		e->real = -0.7;
		e->zoom = 1;
	}
	if (strcmp(e->argv, "mandel") == 0 || strcmp(e->argv, "ship") == 0)
	{
		e->mandel_x = 1.5;
		e->mandel_y = 0.5;
		e->real = 1.5;
		e->move_x = -0.5;
		e->move_y = 0;
		e->zoom = 0.694444;
	}
	e->ajust = 0;
	e->iter = 50;
	e->mouse_event = 0;
	e->win = mlx_new_window(e->mlx, WIDTH, HEIGHT, "fractol");
}

void		malloc_fractal(t_env *e)
{
	if (!(e->point = (t_point *)malloc(sizeof(t_point))))
		fractol_er("Fractol: error malloc");
	if (!(e->pointa = (t_point *)malloc(sizeof(t_point))))
		fractol_er("Fractol: error malloc");
	if (!(e->pointb = (t_point *)malloc(sizeof(t_point))))
		fractol_er("Fractol: error malloc");
	if (!(e->points = (t_point *)malloc(sizeof(t_point))))
		fractol_er("Fractol: error malloc");
	if (!(e->calc = (t_calc *)malloc(sizeof(t_calc))))
		fractol_er("Fractol: error malloc");
	if (!(e->mlx = mlx_init()))
		fractol_er("mlx init error\n");
}
