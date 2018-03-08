/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   main.c                                             :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:55:58 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 16:39:40 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/fractol.h"

void		select_frtl(t_env *e)
{
	if (strcmp(e->argv, "julia") == 0)
	{
		e->fractal = 1;
		draw_julia(e);
	}
	else if (strcmp(e->argv, "mandel") == 0)
	{
		e->fractal = 2;
		draw_mandel(e);
	}
	else if (strcmp(e->argv, "julia2") == 0)
	{
		e->fractal = 3;
		draw_julia(e);
	}
	else if (strcmp(e->argv, "ship") == 0)
	{
		e->fractal = 4;
		ship_draw_1(e);
	}
}

int			expose_hook(t_env *e)
{
	e->img = mlx_new_image(e->mlx, WIDTH, HEIGHT);
	select_frtl(e);
	mlx_put_image_to_window(e->mlx, e->win, e->img, 0, 0);
	mlx_destroy_image(e->mlx, e->img);
	show_help(e);
	return (0);
}

int			main(int argc, char **argv)
{
	t_env	e;

	if (argc != 2)
		fractol_er("Usage; /fractol <julia> or <mandel> or <julia2> or <ship>");
	if (strcmp(argv[1], "mandel") == 0 || strcmp(argv[1], "julia") == 0 ||\
			strcmp(argv[1], "julia2") == 0 || strcmp(argv[1], "ship") == 0)
		e.argv = argv[1];
	else
		fractol_er("Usage; /fractol <julia> or <mandel> or <julia2> or <ship>");
	malloc_fractal(&e);
	init_fractol(&e);
	mlx_expose_hook(e.win, expose_hook, &e);
	mlx_mouse_hook(e.win, mouse_hook, &e);
	mlx_hook(e.win, 6, 1L >> 6, mouse_motion, &e);
	mlx_hook(e.win, 2, 3, key_hook, &e);
	mlx_loop(e.mlx);
	free(&e);
}
