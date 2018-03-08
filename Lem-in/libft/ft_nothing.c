/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_nothing.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/09 14:32:53 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/09 14:37:01 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

static t_buff	*ft_starlette(int *i, t_buff *arg, t_chain *ap)
{
	t_buff *new;

	new = (t_buff *)malloc(sizeof(t_buff));
	if (ft_presence_star(g_s, *i + 2) == 2 || g_s[*i] == '*')
	{
		if (g_s[*i] == '0')
			arg->buf = ft_remplissage('0', ft_atoi(ap->arg) - 1);
		else
			arg->buf = ft_remplissage(' ', ft_atoi(ap->arg) - 1);
		ap = ap->next;
	}
	if (ft_presence_star(g_s, *i + 2) == 3)
	{
		if (g_s[*i] == '0')
			arg->buf = ft_remplissage('0', ft_atoi(ap->arg) - 1);
		else
			arg->buf = ft_remplissage(' ', ft_atoi(ap->arg) - 1);
		ap = (ap->next)->next;
	}
	arg->type = ft_strnew(1);
	arg->next = new;
	arg->arg = ft_strnew(1);
	arg->c = 0;
	return (new);
}

static int		ft_moinss(int i)
{
	while (g_s[i] && g_s[i] != '%')
	{
		if (g_s[i] == '-')
			return (1);
		i--;
	}
	return (0);
}

static int		ft_i(int i, char **buf)
{
	int		moins;
	char	*tmp;

	tmp = ft_strnew(2);
	moins = ft_moinss(i);
	if (ft_strchr(ft_strdup("0123456789."), g_s[i]) != 0)
	{
		while (g_s[i] && ft_strchr(ft_strdup("0123456789."), g_s[i]) != 0)
			i++;
	}
	if (g_s[i] == '%')
		*buf = ft_strjoin(*buf, ft_strdup("%"));
	if (moins == 1)
	{
		tmp[0] = g_s[i];
		*buf = ft_strjoin(tmp, *buf);
	}
	else
		i--;
	return (i);
}

t_buff			*ft_dolse(int *i, t_buff *arg, t_chain *ap)
{
	t_buff *new;

	new = (t_buff *)malloc(sizeof(t_buff));
	while (g_s[*i] && ft_strchr(ft_strdup(" #-+"), g_s[*i]) != 0)
		*i += 1;
	if (ft_presence_star(g_s, *i + 2) >= 2 || g_s[*i] == '*')
		return (ft_starlette(i, arg, ap));
	while (ft_strchr(ft_strdup("*#-+"), g_s[*i]) != 0)
		*i += 1;
	if (ft_strchr(ft_strdup("0123456789"), g_s[*i]))
	{
		if (g_s[*i] == '0')
			arg->buf = ft_remplissage('0', ft_atoi(&g_s[*i]) - 1);
		else
			arg->buf = ft_remplissage(' ', ft_atoi(&g_s[*i]) - 1);
	}
	else
		arg->buf = ft_strnew(1);
	arg->type = ft_strdup("rien");
	arg->next = new;
	arg->arg = ft_strnew(1);
	arg->c = 0;
	*i = ft_i(*i, &arg->buf);
	return (new);
}

int				ft_nothing(int i)
{
	while (g_s[i] && ft_strchr(ft_strdup("*#0-+ hljz0123456789."), g_s[i]) != 0)
		i++;
	if (ft_strchr(ft_strdup("sdciuoxOUXDpCSb"), g_s[i]) != 0)
		return (0);
	return (1);
}
