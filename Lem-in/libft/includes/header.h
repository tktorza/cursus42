/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   header.h                                           :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/01/25 16:17:21 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/14 14:11:08 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef HEADER_H
# define HEADER_H

# include "libft.h"
# include "get_next_line.h"
# include <stdarg.h>
# include <wchar.h>

typedef struct			s_char
{
	char				*preci;
	char				*large;
	char				*hexa;
	char				*arg;
	int					moins;
	int					star;
}						t_char;

typedef struct			s_chain
{
	char				*type;
	char				*arg;
	struct s_chain		*next;
}						t_chain;

typedef struct			s_buff
{
	char				*type;
	char				*buf;
	char				*arg;
	int					moins;
	char				c;
	struct s_buff		*next;
}						t_buff;

int g_total;
int g_moins;
int *g_chars;
int g_preci;
int g_x;
const char *g_s;

typedef char			*(*t_fun_ptr)(va_list*, int);

char					*ft_coloriste(const char *s, int i);
t_buff					*ft_color(const char *s, int *i, t_buff *arg);
void					ft_etoile(const char *s, int *i);
t_buff					*ft_dolse(int *i, t_buff *arg, t_chain *ap);
int						ft_nothing(int i);
void					ft_argo(int i, t_chain *chain, va_list *ap,\
		const char *s);
char					*ft_false(int *i, int tab[8]);
char					*ft_preci_for_p(char *str);
int						ft_sign(const char *s, int i, char **value);
t_buff					*ft_printfou(t_buff *arg, t_chain *chain, \
		const char *s);
void					ft_initialize(const char *s);
t_buff					*cas_3(int *i, t_buff *arg, t_buff *new);
t_buff					*cas_2(int *i, t_buff *arg, t_buff *new);
t_buff					*cas_1(int *i, t_buff *arg, t_buff *new);
void					ft_stars(int *i, t_char *all, t_chain *ap, int tab[8]);
void					ft_ini(t_buff *arg, int *i, char **tmp, char **final);
void					ft_chain_bis(const char *s, va_list *ap, \
		int *i, t_chain *chain);
void					ft_changer(const char *s, int i, int *x, char **str);
char					*ft_preci_other(t_char *all, int i, char **s\
		, char **preci);
void					ft_precision_bis(const char *str, int i, char **s\
		, char **preci);
t_buff					*ft_pursuit(const char *s, int *i, t_buff *arg);
int						ft_cas(const char *s, int i);
t_buff					*ft_suite(long long nb, const char *s, int *i\
		, t_buff *arg);
long long				ft_qqchose(const char *s, int i);
int						*ft_mallocation(void);
char					*ft_preci_gs(char *s, int preci);
int						ft_compt_char(char *s);
int						ft_pourcent(const char *s, int *i);
int						ft_verif(const char *s, int i);
char					*final_arg(const char *s, int i, t_chain ap\
		, t_char *all);
char					*ft_spec_large(int tab[8], t_char *all\
		, const char *s, int i);
int						ft_presence_star(const char *s, int i);
char					*ft_largeur(int tab[8], t_char *all\
		, const char *s, int i);
char					*ft_c_large(int tab[8], t_char *all);
int						ft_dolls(const char *str, int i, t_chain *ap\
		, t_char *all);
char					*ft_precision(const char *str, int i\
		, t_char *all, t_chain *ap);
char					*ft_strnew_diff(long long size);
long long				ft_compt_diff(long long nbr);
char					*ft_hexagone(const char *s, int *i, int tab[8]\
		, t_char *ap);
t_buff					*ft_complication(const char *s, int *i, t_buff *arg);
int						ft_check(const char *s, va_list *ap);
t_chain					*ft_chain_next(t_chain *chain);
char					*ft_type(const char *s, int i);
t_chain					*ft_list_chain(const char *s, va_list *ap, int i);
int						ft_folimpress(t_buff *arg);
t_buff					*ft_arg_list(const char *s, int *i, t_buff *arg);
void					ft_special(t_char *all, int plus, int space);
char					*ft_chainz(char **mask, int i);
char					*ft_copyy(char *s, char *base);
char					*ft_spec(char *s);
char					*ft_reverse(char *str);
char					*ft_putlchar_etoile(va_list *ap, int modif);
char					*ft_final(t_char *all, int *tab, char c);
int						ft_modif(const char *s, int i);
char					*ft_arg(const char *s, int *index, va_list *ap);
void					ft_clean(int *tab, int size);
void					ft_increment(const char *s, int *i);
void					ft_tabint(int *att, const char *s, int *i);
char					*ft_remplissage(char c, int size);
char					*buff_att(const char *s, int *i, t_chain *ap\
		, int tab[8]);
char					*ft_attribut(const char *s, int *i, t_chain *ap);
t_buff					*ft_action(const char *s, int *i, t_chain *ap\
		, t_buff *arg);
char					*ft_itoa_diff(long long n);
char					*ft_itoa_base_bis(unsigned long long value, int base);
void					ft_create_tab(char *(**tab)());
void					new_traitement(char c, va_list *ap);
char					*ft_itoa_base(unsigned long long value, int base);
char					*ft_itoa_base_maj(unsigned long long value, int base);
int						ft_printf(const char *s, ...);
char					*ft_putchar_v(va_list *ap, int modif);
char					*ft_putstr_v(va_list *ap, int modif);
char					*ft_putnbr_v(va_list *ap, int modif);
char					*ft_put_oct(va_list *ap, int modif);
char					*ft_put_hexa_maj(va_list *ap, int modif);
char					*ft_put_deci(va_list *ap, int modif);
char					*ft_put_hexa(va_list *ap, int modif);
char					*ft_binary(va_list *ap, int modif);
char					*ft_putvoid(va_list *ap, int modif);
char					*ft_putlchar(va_list *ap, int modif);

#endif
