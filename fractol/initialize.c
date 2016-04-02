/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   initialize.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/23 18:04:39 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/01 03:47:28 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "Includes/fractol.h"

void	e_initialize(t_env *e)
{
	if (e->frac == 'J')
	{
		e->zoom = 1;
		e->esc_right = 0;
		e->esc_up = 0;
	e->it_max = 150;
	e->c1 = -0.7;
	e->c2 = 0.27015;
	e->longer = 1000;
	e->large = 1000;
	}
	else if (e->frac == 'M' || e->frac == 'F')
	{

	e->zoom = 300;
	e->esc_right = 50;
	e->esc_up = 50;
	e->it_max = 150;

	e->longer = 1000;
	e->large = 1000;
	}
}
