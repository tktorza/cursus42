/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fractol.h                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:56:40 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 16:04:48 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef FRACTOL_H
# define FRACTOL_H
# include <math.h>

# define WIDTH 800
# define HEIGHT 800
# define ABS(x)		(x >= 0 ? x : -x)

# include <pthread.h>
# include <mlx.h>
# include "color.h"
# include "../libft/includes/libft.h"
# include <stdlib.h>

typedef struct s_color		t_color;
struct						s_color
{
	int						r;
	int						g;
	int						b;
};

typedef struct s_point		t_point;
struct						s_point
{
	int						x;
	int						y;
};

typedef struct s_calc		t_calc;
struct						s_calc
{
	double					newre;
	double					newim;
	double					oldre;
	double					oldim;
};

typedef struct s_env		t_env;
struct						s_env
{
	void					*mlx;
	void					*win;
	void					*img;
	char					*pixel_image;
	int						bpp;
	int						s_line;
	int						ed;
	int						fractal;
	char					*argv;
	int						ajust;
	t_point					*point;
	t_point					*points;
	t_point					*pointa;
	t_point					*pointb;
	t_calc					*calc;
	double					zoom;
	double					imnbr;
	double					real;
	double					mandel_x;
	double					mandel_y;
	double					move_x;
	double					move_y;
	int						iter;
	int						mouse_event;

};

int							look_put(void *mlx);
t_color						get_color(t_color color, t_env *e, int i, float c);
int							expose_hook(t_env *e);
int							key_hook(int keycode, t_env *e);
int							mouse_motion(int x, int y, t_env *e);
int							mouse_hook(int button, int x, int y, t_env *e);
void						draw_reload(t_env *e);
void						draw_pixel(t_env *e, int x, int y, t_color color);
void						show_help(t_env *e);
void						draw_julia(t_env *e);
void						fractol_er(char *str);
void						draw_mandel(t_env *e);
void						julia_thread(t_env *e, int mod);
void						*draw_1(void *list);
void						*draw_2(void *list);
void						*draw_3(void *list);
void						*draw_4(void *list);
void						draw_mandel(t_env *e);
void						*mandel_draw_1(void *list);
void						*mandel_draw_2(void *list);
void						*mandel_draw_3(void *list);
void						ship_draw_1(void *list);
void						calc_mandel(int *i, t_env *e);
void						mandel_thread(t_env *e, int mod);
void						init_fractol(t_env *e);
void						malloc_fractal(t_env *e);
#endif
