/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_check.c                                         :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 12:31:12 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/23 12:43:44 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

int		ft_check(const char *s, va_list *ap)
{
	int		i;

	ap = NULL;
	i = 0;
	while (s[i])
	{
		if (s[i] == '%' && (s[i + 1] != '%' && (s[i + 1] != ' ' \
						&& s[i + 2] != '%')))
		{
			i++;
			while (ft_strchr(ft_strdup("#0-+ hljz0123456789."), s[i]) != 0)
				i++;
			if (ft_strchr(ft_strdup("sdciouxOUXDpCSb"), s[i]) == 0)
				return (1);
		}
		i++;
	}
	return (0);
}

int		ft_verif(const char *s, int i)
{
	if (s[i] == '%' && \
			ft_strchr(ft_strdup("bsdciouxOUX*DpCS#0-+ hljz0123456789.")\
			, s[i + 1]) != 0 && s[i + 1])
	{
		i++;
		while (s[i] != '%' && s[i] &&\
				ft_strchr(ft_strdup("bsdciouxOUX*DpCS#0-+ hljz0123456789."),\
					s[i]) != 0)
			i++;
		if (s[i] == '%' &&\
				ft_strchr(ft_strdup("sdciouxOUXDpCSb"), s[i - 1]) != 0)
			return (1);
		if (!s[i] && ft_strchr(ft_strdup("sdciouxOUXDpCSb"), s[i - 1]) != 0)
			return (1);
	}
	return (0);
}
