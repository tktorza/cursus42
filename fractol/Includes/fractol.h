/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fractol.h                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/23 16:03:25 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/01 03:39:40 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef FRACTOL_H
# define FRACTOL_H

# include "../libft/includes/libft.h"
# include "../libft/includes/header.h"
# include "../libft/includes/get_next_line.h"
# include "mlx.h"
# include "math.h"

typedef struct		s_milieu
{
		double		x;
		double		y;
}					t_milieu;

typedef struct		s_var
{
	int				x;
	int				y;
	int				dx;
	int				sx;
	int				dy;
	int				sy;
	int				error;
	int				e2;
}					t_var;

typedef struct		s_tools
{
	double			new_re;
	double			prev_re;
	double			new_im;
	double			prev_im;
	int				i;
	double			ux;
	double			uy;
	double			x;
	double			y;
	double			c_re;
	double			c_im;
}					t_tools;

typedef struct		s_env
{
		void		*mlx;
		void		*win;
		void		*image;
		char		*pix;
		char		frac;
		int			longer;
		int			large;
		double		zoom;
		int			esc_up;
		int			esc_right;
		int			jk;
		int			jo;
		int			jp;
		int			it_max;
		int			j_long;
		int			j_large;
		double		c1;
		double		c2;
}					t_env;

typedef struct		s_color
{
	int				b;
	int				g;
	int				r;
}					t_color;


void				multi_thread(t_env *e);
void				other_thread(t_env *e);
void				mandel_thread(t_env *e);
void				flocon(t_env *e);
void				draw_pixel(t_env *e, int x, int y, t_color color);
int					expose_hook(t_env *e);
int					key_interact(int keycode, t_env *e);
int					mouse_interact(int keycode, t_env *e);
int					look_put(void *mlx);
void				mandelbrot(t_env *e);
void				julia(t_env *e);
void				e_initialize(t_env *e);

#endif
