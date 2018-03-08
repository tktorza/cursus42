/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_anexes_2.c                                      :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 13:09:11 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:58:59 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

void	ft_ini(t_buff *arg, int *i, char **tmp, char **final)
{
	while (g_s[*i] && g_s[*i] != '%')
	{
		if (ft_coloriste(g_s, *i) == NULL)
		{
			*tmp[0] = g_s[*i];
			*final = ft_strjoin(*final, *tmp);
			*i += 1;
		}
		else
			break ;
	}
	arg->buf = (char *)malloc(sizeof(char) * (ft_strlen(*final) + 1));
	arg->buf = *final;
	arg->type = ft_strnew(1);
	arg->c = 0;
	arg->arg = ft_strnew(1);
	*i -= 1;
}

void	ft_chain_bis(const char *s, va_list *ap, int *i, t_chain *chain)
{
	while (ft_strchr(ft_strdup("bsdciou*xOUXDpCS"), s[*i]) == 0)
		*i += 1;
	if (s[*i] == '*')
	{
		chain->arg = ft_itoa((va_arg(*ap, int)));
		chain->type = ft_strdup("d");
		chain = ft_chain_next(chain);
	}
	while (ft_strchr(ft_strdup("bsdciouxOUXDpCS"), s[*i]) == 0)
		*i += 1;
	chain->arg = ft_arg(s, i, ap);
	chain->type = ft_type(s, *i);
	chain = ft_chain_next(chain);
}

void	ft_changer(const char *s, int i, int *x, char **str)
{
	int y;

	y = 0;
	while (ft_strdup("hl")[y])
	{
		if (s[i - 2] == ft_strdup("hl")[y])
		{
			*str[*x] = ft_strdup("hl")[y];
			x += 1;
		}
		y++;
	}
}

void	ft_precision_bis(const char *str, int i, char **s, char **preci)
{
	if (g_preci == 0)
		*s = ft_strnew(1);
	else if (g_preci <= (int)ft_strlen(*s) && str[i] == 's' \
			&& str[i - 1] != 'l')
		*s = ft_strsub(*s, 0, g_preci);
	else if (g_preci <= (int)ft_strlen(*s) && (str[i] == 'S' \
				|| (str[i] == 's' && *s[i - 1] == 'l')))
		*s = ft_preci_gs(*s, g_preci);
	else
		*preci = *s;
}

char	*ft_preci_other(t_char *all, int i, char **s, char **preci)
{
	if (g_preci == 0)
		return (ft_strnew(1));
	if (g_preci > (int)ft_strlen(*s))
	{
		if (g_s[i] == 'o' || g_s[i] == 'O')
		{
			*preci = (char *)malloc(sizeof(char) * \
			(g_preci - ft_strlen(*s) - ft_strlen(all->hexa)));
			*preci = ft_remplissage('0', \
			(g_preci - ft_strlen(*s) - ft_strlen(all->hexa)));
		}
		else
		{
			*preci = (char *)malloc(sizeof(char) * (g_preci - ft_strlen(*s)));
			*preci = ft_remplissage('0', (g_preci - ft_strlen(*s)));
		}
		return (ft_strjoin(*preci, *s));
	}
	return (0);
}
