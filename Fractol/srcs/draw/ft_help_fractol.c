/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_help_fractol.c                                  :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:58:49 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 15:58:54 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/fractol.h"

void			fractol_er(char *str)
{
	ft_putstr_fd(str, 2);
	exit(1);
}

void			show_help(t_env *e)
{
	mlx_string_put(e->mlx, e->win, 20, 10, 0xFFFFFF, "FRACTAL: ");
	mlx_string_put(e->mlx, e->win, 120, 10, 0x33FF00, e->argv);
	mlx_string_put(e->mlx, e->win, 20, 40, 0xFFFFFF, "TOUCHES:");
	mlx_string_put(e->mlx, e->win, 40, 65, 0x66FF00,
		"- ZOOM           Page Up / Down / Scroll");
	mlx_string_put(e->mlx, e->win, 40, 85, 0x66FF00,
		"- MOVE           Pad Fleches");
	mlx_string_put(e->mlx, e->win, 40, 105, 0x66FF00,
		"- ValueFractal   + / -");
	mlx_string_put(e->mlx, e->win, 40, 125, 0x66FF00,
		"- ColorEffect    1 / 2");
	mlx_string_put(e->mlx, e->win, 40, 145, 0x66FF00,
		"- Reset          SPACE");
	mlx_string_put(e->mlx, e->win, 40, 165, 0x66FF00,
		"- Motion         END");
	mlx_string_put(e->mlx, e->win, 40, 185, 0x66FF00,
		"- Quitter        Echap");
}
