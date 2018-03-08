/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fdf_convert_iso.c                                  :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/18 16:39:32 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/22 16:20:54 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/fdf.h"

int		conv_x(int x, int y, t_env *e)
{
	return (((x - y) * e->zoom) + e->esc_right);
}

int		conv_y(int x, int y, int z, t_env *e)
{
	return (((x + y - (z * e->height)) * e->zoom) / 2 + e->esc_up);
}
