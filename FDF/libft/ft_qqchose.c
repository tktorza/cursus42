/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_qqchose.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 15:05:04 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/23 15:06:06 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

t_buff		*ft_pursuit(const char *s, int *i, t_buff *arg)
{
	arg->arg = NULL;
	arg->type = NULL;
	if (ft_strchr(ft_strdup("#.0+-"), s[*i]) != 0)
		*i += 1;
	ft_increment(s, i);
	arg->buf = ft_strnew(1);
	arg->buf[0] = s[*i];
	return (arg);
}

int			ft_cas(const char *s, int i)
{
	if (s[i] == '.')
	{
		i++;
		while (s[i] && s[i] >= '0' && s[i] <= '9')
			i++;
		if (s[i] == '%')
			return (1);
	}
	return (0);
}

long long	ft_qqchose(const char *s, int i)
{
	int		nb;
	int		presence;

	nb = 0;
	presence = 0;
	if (ft_strchr(ft_strdup("0-+# "), s[i]) != 0)
		i++;
	if (ft_strchr(ft_strdup("0123456789"), s[i]) != 0)
	{
		if (s[i] == '0')
			presence++;
		nb = ft_atoi(&s[i]);
	}
	ft_increment(s, &i);
	if (ft_strchr(ft_strdup("bsdciouxOUXDp*CS#i.-+ hljz"), s[i]) != 0)
		return (-1000);
	else
	{
		if (presence == 1)
			return (-nb);
		return (nb);
	}
}

t_buff		*ft_suite(long long nb, const char *s, int *i, t_buff *arg)
{
	int		moins;
	char	*m;

	moins = 0;
	if (ft_strchr(ft_strdup("0-+# "), s[*i]) != 0)
	{
		moins++;
		*i += 1;
	}
	arg->arg = NULL;
	arg->type = NULL;
	ft_increment(s, i);
	*i -= 1;
	if (nb < 0)
		arg->buf = ft_remplissage('0', -nb + 1);
	else
		arg->buf = ft_remplissage(' ', nb - 1);
	if (moins != 0)
	{
		*i += 1;
		m = ft_strnew(1);
		m[0] = s[*i];
		arg->buf = ft_strjoin(m, arg->buf);
	}
	return (arg);
}
