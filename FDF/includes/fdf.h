/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fdf.h                                              :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/14 12:47:48 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/22 18:10:50 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef FDF_H
# define FDF_H

# include "../libft/includes/header.h"
# include <fcntl.h>
# include "mlx.h"
# include "math.h"

typedef struct			s_coord
{
	int				x_1;
	int				x_2;
	int				y_1;
	int				y_2;
	int				z_1;
	int				z_2;
}						t_coord;

typedef struct			s_var
{
	int					x;
	int					y;
	int					dx;
	int					sx;
	int					dy;
	int					sy;
	int					error;
	int					e2;
}						t_var;

typedef struct			s_point
{
	int				x;
	int				y;
}						t_point;

typedef struct			s_line
{
	int				max;
	int				*line;
	struct s_line	*next;
}						t_line;

typedef struct			s_env
{
	int				i;
	int				x;
	int				pts;
	int				longer;
	int				large;
	struct s_line	*line;
	void			*image;
	void			*mlx;
	void			*win;
	char			*pix;
	int				zoom;
	int				max;
	char			*str;
	int				min;
	int				jk;
	int				jo;
	int				jp;
	int				esc_up;
	int				esc_right;
	double			height;
	int				onetwo;
	int				c1;
	int				c2;
	int				c3;
	int				height_going;
	int				zed;
	int				start;
	int				xmax;
	int				ymax;
}						t_env;

typedef struct			s_color
{
	int				b;
	int				g;
	int				r;
}						t_color;

int						look_put(void *mlx);
void					ft_anim(t_env *e);
int						key_interact(int keycode, t_env *e);
int						fdf_error(void);
int						look_put(void *mlx);
void					colorate(t_env *e);
int						outside_window(t_env *e, int x, int y);
void					draw_pixel(t_env *e, int x, int y, t_color color);
int						expose_hook(t_env *e);
void					line(t_point a, t_point b, t_env *env);
void					fdf_trace_right(t_point ok, t_env *e, t_line *line);
void					fdf_trace_down(t_point ok, t_env *e, t_line *line);
void					print_line(t_point a, t_point b, t_env *e);
t_coord					*ft_coord2(int x, int y, int x_2, int y_2);
t_coord					*ft_coords(t_coord *c, t_line *line);
void					e_initialize(t_env *e);
int						conv_x(int x, int y, t_env *e);
int						conv_y(int x, int y, int z, t_env *e);
void					fdf_segment(t_line *line, t_env *e);
char					*data_change(char *s, t_line *line);
int						key_interact(int keycode, t_env *e);
void					fdf_draw(t_line *line, t_env *e);
int						find_point(t_line *line, int p_x, int p_y);
#endif
