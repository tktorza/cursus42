/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   main.c                                             :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/23 16:01:40 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/01 03:34:04 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "Includes/fractol.h"

int		check_fractale(char **argv)
{
	if (ft_strcmp(ft_strdup("Julia"), argv[1]) == 0 || \
			(ft_strcmp(ft_strdup("Mandelbrot"), argv[1]) == 0) || \
			(ft_strcmp(ft_strdup("Flocon"), argv[1]) == 0))
		return (1);
	return (0);
}

int		error_fracol(void)
{
	ft_printf("Fractole can only be used with {cyan}Julia{eoc}'s \
			{red}fractale{eoc}, {green}Mandelbrot{eoc}'s{red} \
			fractal{eoc} or {magenta}Flocon{eoc}'s{red} fractal.\n");
			return (0);
}

char	fractale_type(char **argv)
{
	if (ft_strcmp(ft_strdup("Julia"), argv[1]) == 0)
		return ('J');
	else if (ft_strcmp(ft_strdup("Mandelbrot"), argv[1]) == 0)
		return ('M');
	else if (ft_strcmp(ft_strdup("Flocon"), argv[1]) == 0)
		return ('F');
	return (0);
}

int		main(int argc, char **argv)
{
	t_env e;

	if (argc == 2 && check_fractale(argv) == 1)
	{
		e.frac = fractale_type(argv);
		e.mlx = mlx_init();
		e_initialize(&e);
		e.win = mlx_new_window(e.mlx, e.large, e.longer, "fractol");
		mlx_loop_hook(e.mlx, look_put, &e);
		mlx_hook(e.win, 2, 3, key_interact, &e);
//		mlx_mouse_hook(e.win, mouse_interact, &e);
		mlx_loop(e.mlx);
	}
	else
		error_fracol();
	return (0);
}
