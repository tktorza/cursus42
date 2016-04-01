#include "Includes/fractol.h"
#include <pthread.h>

int		propulsive(t_env *e, t_tools *t)
{
	t->new_re = 1.5 * (t->x - e->large / 2) / (0.5 * e->zoom * e->large) + e->esc_right;
	t->new_im = (t->y - e->longer / 2) / (0.5 * e->zoom * e->longer) + e->esc_up;
	t->i = 0;
	while (t->i < e->it_max)
	{
		t->prev_re = t->new_re;
		t->prev_im = t->new_im;
		t->new_re = t->prev_re * t->prev_re - t->prev_im * t->prev_im + t->c_re;
		t->new_im = 2 * t->prev_re * t->prev_im + t->c_im;
		if (t->new_re * t->new_re + t->new_im * t->new_im > 4)
			break;
		
		t->i += 1;
	}
	return (1);
}


void	*julia1(void *list)
{
	t_tools	*t;
		t_env *e;

	t = (t_tools *)malloc(sizeof(t_tools));
		e = list;
	t->y = 0;
	t->c_re = e->c1;
	t->c_im = e->c2;
	while (t->y < e->longer / 2)
	{
		t->x = 0;
		while ( t->x < e->large / 2)
		{
			propulsive(e, t);
			draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {30 * t->i, 25 * t->i, 255 * t->i});
			t->x += 1;
		}
		t->y += 1;
	}
	return (NULL);
}

void	*julia2(void *list)
{
	t_tools	*t;
		t_env *e;

	t = (t_tools *)malloc(sizeof(t_tools));
		e = list;
	t->y = 0;
	t->c_re = e->c1;
	t->c_im = e->c2;
	while (t->y < e->longer / 2)
	{
		t->x = e->large / 2;
		while ( t->x < e->large)
		{
			propulsive(e, t);
			draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {30 * t->i, 25 * t->i, 255 * t->i});
			t->x += 1;
		}
		t->y += 1;
	}
	return (NULL);
}

void	*julia3(void *list)
{
	t_tools	*t;
		t_env *e;

	t = (t_tools *)malloc(sizeof(t_tools));
		e = list;
	t->y = e->longer / 2;
	t->c_re = e->c1;
	t->c_im = e->c2;
	while (t->y < e->longer)
	{
		t->x = 0;
		while ( t->x < e->large / 2)
		{
			propulsive(e, t);
			draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {30 * t->i, 25 * t->i, 255 * t->i});
			t->x += 1;
		}
		t->y += 1;
	}
	return (NULL);
}

void	*julia4(void *list)
{
	t_tools	*t;
		t_env *e;

	t = (t_tools *)malloc(sizeof(t_tools));
		e = list;
	t->y = e->longer / 2;
	t->c_re = e->c1;
	t->c_im = e->c2;
	while (t->y < e->longer)
	{
		t->x = e->large / 2;
		while ( t->x < e->large)
		{
			propulsive(e, t);
			draw_pixel(e, t->x + e->esc_right, t->y + e->esc_up, (t_color) {30 * t->i, 25 * t->i, 255 * t->i});
			t->x += 1;
		}
		t->y += 1;
	}
	return (NULL);
}

void	multi_thread(t_env *e)
{
	pthread_t a1, a2, a3, a4;
	pthread_create(&a1, NULL, julia1, e);
	pthread_create(&a2, NULL, julia2, e);
	pthread_create(&a3, NULL, julia3, e);
	pthread_create(&a4, NULL, julia4, e);
	pthread_join(a1, NULL);
	pthread_join(a2, NULL);
	pthread_join(a3, NULL);
	pthread_join(a4, NULL);
}
