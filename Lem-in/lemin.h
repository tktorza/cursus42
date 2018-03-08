/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   lemin.h                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:55:42 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:55:44 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef LEMIN_H
# define LEMIN_H

# include <stdlib.h>
# include <unistd.h>
# include <fcntl.h>
# include "libft/includes/get_next_line.h"
# include "libft/includes/header.h"

typedef struct			s_sett
{
	char				*str;
	struct s_sett		*next;
}						t_sett;

typedef struct			s_link
{
	char				*str;
	struct s_link		*next;
}						t_link;

typedef struct			s_bloc
{
	char				*str;
	int					nb;
	int					ant_nb;
	struct s_bloc		*next;
}						t_bloc;

typedef struct			s_aff
{
	int					final;
	char				*bloc;
	char				*room;
	int					index;
	struct s_aff		*next;
}						t_aff;

typedef struct			s_file
{
	char				*name;
	int					ant;
	char				**link;
	int					x;
	int					y;
	struct s_file		*next;
}						t_file;

typedef struct			s_tools
{
	int					total;
	int					rest;
	int					div;
	int					path;
}						t_tools;

typedef struct			s_iter
{
	int					i;
	int					j;
	int					k;
	char				*str;
	char				*tmp;
}						t_iter;

t_iter					*free_iter(t_iter *iter);

t_sett					*set_clean(t_sett *thing);
t_sett					*ft_cop_listb(t_sett *origin);
t_sett					*next_maillon(t_sett *prev);

t_bloc					*g_bloc;
int						g_ant;
t_link					*g_link;
t_link					*g_origin;
char					*g_end;
char					*g_start;
t_sett					*g_sett_origin;

t_aff					*aff_next(t_aff *aff);
t_aff					*actual_aff(t_aff *origin, t_aff *new);
t_aff					*new_aff(t_aff *tmp, t_bloc *bloc, int *nb);
t_aff					*delete_end(t_aff *new);

t_link					*link_next(t_link *link);
t_link					*short_link(void);
t_link					*short_tab();
t_link					*short_linkend();
t_link					*link_dell(t_link *tmp, char *str);
t_link					*dell_occurrence(t_link *path, t_link *curent);
t_link					*path_selection(t_link *final, t_link *path);
t_link					*generation(t_link *path, t_link *curent);
t_link					*ft_cop_list();
t_link					*ft_selection(t_link *curt, t_link *final);
t_link					*final_path(t_link *final, char *str);
t_link					*short_final(t_link *final);
t_link					*short_fin(t_link *final, t_link *tmp,
	t_link *last, t_link *begin);

t_bloc					*bloc_next(t_bloc *bloc);
t_bloc					*ft_algo(t_link *final);
t_bloc					*ft_path(t_link *tmp);
t_bloc					*ft_calling(void);
t_bloc					*next_path(t_link *final);
t_bloc					*ft_cut_path(t_link *start, t_link *curent,
	t_link *final, t_link *path);

t_file					*file_next(t_file *file);
t_file					*file_start(t_sett *thing, t_file *file);
t_file					*file_end(t_sett *thing, t_file *file);
t_file					*put_file(t_sett *thing, t_file *file);
t_file					*ft_parsing(t_sett *thing);

int						ft_error(void);
int						general_possibility(t_link *start, t_link *path\
		, t_link *curent);
int						ft_check_all(char *str, char *tmp);
int						current_possibility(char *str, t_link *own);
int						check(t_sett *origin);
int						ft_cut_check(t_sett *set, int i, int j, int k);
int						file_coor(char *str, char c);
int						ft_strlen_bis(char *str);
int						ft_checkstr(char *str, char *tmp);
int						check_start(char *str);
int						check_start2(char *str);
int						check_end2(char *str);
int						check_end(char *str);
int						ft_check1(char *str, char *tmp);
int						ft_check2(char *str, char *tmp);
int						ft_check_order(char *str, char *tmp);
int						compt_start(void);
int						compt_end(void);
int						ft_occurance(char *str, char *tmp);
int						ft_compare_path(char *bloc, char *compare);
int						ft_linklen(char *str);
int						go_link(char *str, int i);
int						indexation(t_aff *new);
int						link_compare(char *str, char *tmp);
int						ft_origin_check(t_file *origin);

char					*ft_copy(char *str, char *tmp);
char					*ft_strcut(char *str, char c);
char					*ft_linkrev(char *str);
char					*end(char *str);
char					*final_function(t_link *final);
char					*ft_cuter(char *s, int begin);
char					*half_link(char *str, int i);
char					*room_next(char *s1, char *s2);

void					ft_display(t_bloc *bloc);
void					ft_affich(t_link *test, t_bloc *bloc);
void					ft_affich2(t_sett *origin);
void					doublon_dell(void);
void					init_iter(t_iter *iter);
void					color_ant(int nb);
void					display_on(t_aff *new);
void					ft_do(t_aff *new, t_bloc *bloc);
void					ft_generat(t_link *path, t_link *own,
	t_link *curent, t_link *tmp);
#endif
