/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_printf.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 15:21:57 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/09 19:32:31 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

char	*buff_att(const char *s, int *i, t_chain *ap, int tab[8])
{
	t_char *all;

	all = (t_char *)malloc(sizeof(t_char));
	if (all == NULL)
		return (NULL);
	all->preci = ft_strnew(1);
	all->large = ft_strnew(1);
	all->hexa = ft_strnew(1);
	all->arg = final_arg(s, *i, *ap, all);
	if (all->arg == NULL && (s[*i] == 'S' || s[*i] == 's'))
		all->arg = ft_strdup("(null)");
	all->moins = ft_sign(s, *i, &all->arg);
	all->hexa = ft_hexagone(s, i, tab, all);
	all->preci = ft_precision(s, *i, all, ap);
	if (ft_presence_star(s, *i) == 2 || ft_presence_star(s, *i) == 3)
		ft_stars(i, all, ap, tab);
	else
		all->large = ft_largeur(tab, all, s, *i);
	if (s[*i] == 'd' || s[*i] == 'i' || s[*i] == 'D')
		ft_special(all, tab[3], tab[4]);
	return (ft_final(all, tab, s[*i]));
}

char	*ft_attribut(const char *s, int *i, t_chain *ap)
{
	int		att[8];
	char	*buf;

	ft_clean(att, 7);
	ft_tabint(att, s, i);
	ft_etoile(s, i);
	if (s[*i] <= '9' && s[*i] >= '0')
	{
		att[5] = ft_atoi(&s[*i]);
		ft_increment(s, i);
	}
	ft_increment(s, i);
	if (s[*i] == '.')
	{
		att[6] = ft_atoi(&s[*i + 1]);
		*i += 1;
		ft_increment(s, i);
	}
	if (ft_strchr(ft_strdup("sdciouxOUXDpCShljz*"), s[*i]) == 0)
		return (ft_false(i, att));
	while (s[*i] && ft_strchr(ft_strdup("sdciouxOUXDpCSb"), s[*i]) == 0)
		*i += 1;
	g_preci = att[6];
	buf = buff_att(s, i, ap, att);
	return (buf);
}

t_buff	*ft_action(const char *s, int *i, t_chain *ap, t_buff *arg)
{
	t_buff		*new;

	new = (t_buff *)malloc(sizeof(t_buff));
	arg->next = (t_buff *)malloc(sizeof(t_buff));
	new->next = (t_buff *)malloc(sizeof(t_buff));
	if (ft_nothing(*i) == 1)
		return (ft_dolse(i, arg, ap));
	if (ft_strchr(ft_strdup("sdciouxOUXDpCSb"), s[*i]) != 0)
		arg->buf = ap->arg;
	else
		arg->buf = ft_attribut(s, i, ap);
	arg->type = (char *)malloc(sizeof(char) * (ft_strlen(ap->type) + 1));
	arg->type = ap->type;
	arg->next = new;
	arg->arg = ap->arg;
	arg->c = s[*i];
	*ap = *ap->next;
	return (new);
}

int		ft_printf(const char *s, ...)
{
	int		i;
	va_list	ap;
	t_buff	*arg;
	t_buff	*origin;
	t_chain	*chain;

	ft_initialize(s);
	i = 0;
	va_start(ap, s);
	chain = ft_list_chain(s, &ap, i);
	arg = (t_buff *)malloc(sizeof(t_buff));
	if (arg != NULL)
	{
		origin = arg;
		arg = ft_printfou(arg, chain, s);
		arg->next = NULL;
		return (ft_folimpress(origin));
	}
	return (0);
}
