/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_printf_anexes_2.c                               :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 16:07:04 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/09 20:51:41 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

t_buff		*ft_printfou(t_buff *arg, t_chain *chain, const char *s)
{
	int		i;

	i = 0;
	while (s[i])
	{
		if (s[i] == '%' && s[i + 1] && \
				ft_strchr(ft_strdup("bsdciouxOUX*DpCS#0-+ hljz0123456789.")\
					, s[i + 1]) != 0)
		{
			i++;
			arg = ft_action(s, &i, chain, arg);
			if (s[i] == '.' && s[i + 1] && s[i + 1] == '*')
			{
				chain = chain->next;
				i++;
			}
		}
		else if (ft_coloriste(s, i) != NULL)
			arg = ft_color(s, &i, arg);
		else
			arg = ft_arg_list(s, &i, arg);
		i++;
	}
	return (arg);
}

void		ft_increment(const char *s, int *i)
{
	while (s[*i] && ((s[*i] <= '9' && s[*i] >= '0') || s[*i] == '*'))
		*i += 1;
}

void		ft_tabint(int *att, const char *s, int *i)
{
	char	*base;

	base = ft_strdup("#0-+ ");
	while (s[*i] && ft_strchr(base, s[*i]) != 0)
	{
		if (s[*i] == '#')
			att[0] = 1;
		if (s[*i] == '0')
			att[1] = 1;
		if (s[*i] == '-')
			att[2] = 1;
		if (s[*i] == '+')
			att[3] = 1;
		if (s[*i] == ' ')
			att[4] = 1;
		*i += 1;
	}
	if (att[2] == 1)
		att[1] = 0;
}

char		*ft_remplissage(char c, int size)
{
	int		i;
	char	*s;

	if (size <= 0)
		return (ft_strnew(1));
	i = 0;
	s = (char *)malloc(sizeof(char) * (size + 1));
	while (i < size)
	{
		s[i] = c;
		i++;
	}
	return (s);
}

int			ft_sign(const char *s, int i, char **value)
{
	if ((s[i] == 'd' || s[i] == 'i' || s[i] == 'D') && *value != NULL && \
			*value[0] == '-')
	{
		*value = ft_strsub(*value, 1, ft_strlen(*value));
		return (1);
	}
	return (0);
}
