/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   julia.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/31 22:08:22 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/01 03:49:36 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "Includes/fractol.h"
#include <pthread.h>

int		propulser(t_tools *t, t_env *e)
{
	t->c_re = t->x / e->zoom - 2.1;
	t->c_im = t->y / e->zoom - 1.2;
	t->prev_re = 0;
	t->prev_im = 0;
	t->i = 0;
	while (sqrt(t->prev_re * t->prev_re + t->prev_im * t->prev_im) < 4 && \
			t->i < e->it_max)
	{
		t->new_re = t->prev_re;
		t->prev_re = t->prev_re * t->prev_re - t->prev_im * t->prev_im + t->c_re;
		t->prev_im = 2 * t->prev_im * t->new_re + t->c_im;
		t->i += 1;
	}
	return (1);
}

void	*andel1(void *list)
{
	t_tools	*t;
	t_env	*e;


	e = list;
	t = (t_tools *)malloc(sizeof(t_tools));
	t->y = 0;
	while (t->y < e->large / 2)
	{
		t->x = 0;
		while (t->x < e->longer / 2)
		{
			propulser(t, e);
			if (t->i == e->it_max)
				draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {0, 100 * t->y, 200 / t->x});
			else
				draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {255 * t->i / e->it_max, 0, 0});
			t->x += 1;
		}
		t->y += 1;
	}
	return (NULL);
}

void	*andel2(void *list)
{
	t_tools	*t;
	t_env	*e;


	e = list;
	t = (t_tools *)malloc(sizeof(t_tools));
	t->y = 0;
	while (t->y < e->large / 2)
	{
		t->x = e->longer / 2;
		while (t->x < e->longer)
		{
			propulser(t, e);
			if (t->i == e->it_max)
				draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {0, 100 * t->y, 200 / t->x});
			else
				draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {255 * t->i / e->it_max, 0, 0});
			t->x += 1;
		}
		t->y += 1;
	}
	return (NULL);
}



void	*andel3(void *list)
{
	t_tools	*t;
	t_env	*e;


	e = list;
	t = (t_tools *)malloc(sizeof(t_tools));
	t->y = e->large / 2;
	while (t->y < e->large)
	{
		t->x = 0;
		while (t->x < e->longer / 2)
		{
			propulser(t, e);
			if (t->i == e->it_max)
				draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {0, 100 * t->y, 200 / t->x});
			else
				draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {255 * t->i / e->it_max, 0, 0});
			t->x += 1;
		}
		t->y += 1;
	}
	return (NULL);
}



void	*andel4(void *list)
{
	t_tools	*t;
	t_env	*e;


	e = list;
	t = (t_tools *)malloc(sizeof(t_tools));
	t->y = e-> large / 2;
	while (t->y < e->large)
	{
		t->x = e->longer / 2;
		while (t->x < e->longer)
		{
			propulser(t, e);
			if (t->i == e->it_max)
				draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {0, 100 * t->y, 200 / t->x});
			else
				draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {255 * t->i / e->it_max, 0, 0});
			t->x += 1;
		}
		t->y += 1;
	}
	return (NULL);
}


void	other_thread(t_env *e)
{
	pthread_t a1, a2, a3, a4;
	pthread_create(&a1, NULL, andel1, e);
	pthread_create(&a2, NULL, andel2, e);
	pthread_create(&a3, NULL, andel3, e);
	pthread_create(&a4, NULL, andel4, e);
	pthread_join(a1, NULL);
	pthread_join(a2, NULL);
	pthread_join(a3, NULL);
	pthread_join(a4, NULL);
}
