/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_arg_list.c                                      :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 14:55:32 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/09 21:25:35 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

t_chain		*ft_chain_next(t_chain *chain)
{
	t_chain	*new;

	new = (t_chain *)malloc(sizeof(t_chain));
	chain->next = (t_chain *)malloc(sizeof(t_chain));
	chain->next = new;
	return (new);
}

char		*ft_type(const char *s, int i)
{
	char	*str;
	int		x;
	int		y;

	x = 0;
	y = 0;
	str = ft_strnew(4);
	ft_changer(s, i, &x, &str);
	while (ft_strdup("hljz")[y])
	{
		if (s[i - 1] == ft_strdup("hljz")[y])
			str[x++] = ft_strdup("hljz")[y];
		y++;
	}
	y = 0;
	while (ft_strdup("sdciouxOUXDpCSb")[y])
	{
		if (s[i] == ft_strdup("sdciouxOUXDpCSb")[y])
			str[x] = ft_strdup("sdciouxOUXDpCSb")[y];
		y++;
	}
	return (str);
}

t_chain		*ft_list_chain(const char *s, va_list *ap, int i)
{
	t_chain		*chain;
	t_chain		*origin;

	chain = (t_chain *)malloc(sizeof(t_chain));
	if (chain == NULL)
		return (NULL);
	origin = chain;
	ft_argo(i, chain, ap, s);
	chain = NULL;
	return (origin);
}

int			ft_folimpress(t_buff *arg)
{
	while (arg->next != NULL)
	{
		if ((arg->c == 's' || arg->c == 'S') && arg->buf == NULL)
			ft_putstr(ft_strdup("(null)"));
		else if (arg->c != 'c' && arg->c != 'C')
			ft_putstr(arg->buf);
		if (arg->c == 'c' || arg->c == 'C')
		{
			if (g_moins != 1 && arg->buf != NULL)
				ft_putstr(arg->buf);
			if (arg->arg[0] == '\0')
				ft_putchar('\0');
			if (g_moins == 1 && arg->buf != NULL)
				ft_putstr(arg->buf);
		}
		if (arg->buf == NULL)
			g_total += 6;
		else
			g_total += ft_strlen(arg->buf);
		arg = arg->next;
	}
	return (g_total);
}

t_buff		*ft_arg_list(const char *s, int *i, t_buff *arg)
{
	t_buff	*new;
	char	*tmp;
	char	*final;

	new = (t_buff *)malloc(sizeof(t_buff));
	arg->next = (t_buff *)malloc(sizeof(t_buff));
	arg->next = new;
	final = ft_strnew(1);
	tmp = ft_strnew(1);
	if (s[*i] == '%')
	{
		if (s[*i + 1] == '%')
		{
			final[0] = '%';
			*i += 1;
		}
		*i += 1;
	}
	ft_ini(arg, i, &tmp, &final);
	return (new);
}
