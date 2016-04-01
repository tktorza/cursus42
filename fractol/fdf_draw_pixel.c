/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fdf_draw_pixel.c                                   :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/18 15:49:47 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/01 02:21:55 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "Includes/fractol.h"

void	draw_pixel(t_env *e, int x, int y, t_color color)
{
	if (x < e->large && y < e->longer && x > 0 && y > 0)
	{
	e->pix = mlx_get_data_addr(e->image, &(e->jk), &(e->jo), &(e->jp));
	e->pix[x * e->jk / 8 + y * e->jo] = (unsigned char)color.b;
	e->pix[x * e->jk / 8 + 1 + y * e->jo] = (unsigned char)color.g;
	e->pix[x * e->jk / 8 + 2 + y * e->jo] = (unsigned char)color.r;
}
}
