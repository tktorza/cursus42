/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_complication.c                                  :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 12:30:52 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/02 13:17:02 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

t_buff	*ft_complication(const char *s, int *i, t_buff *arg)
{
	if (s[*i + 1] == '%')
	{
		*i += 1;
		arg->buf = ft_strdup("%");
		arg->arg = NULL;
		arg->type = NULL;
	}
	else
	{
		arg->buf = ft_strnew(1);
		arg->arg = NULL;
		arg->type = NULL;
	}
	return (arg);
}

char	*ft_hexagone(const char *s, int *i, int tab[8], t_char *ap)
{
	char	*str;

	str = ft_strnew(1);
	if ((s[*i] == 'o' || s[*i] == 'O') && tab[0] == 1 && ap->arg != NULL)
	{
		if (ft_atoi(ap->arg) != 0)
			str = ft_strdup("0");
		else if (ft_atoi(ap->arg) == 0 && tab[6] == 0)
			str = ft_strdup("0");
	}
	else if ((s[*i] == 'x' || s[*i] == 'X') && tab[0] == 1 && ap->arg != NULL)
	{
		if (ap->arg[0] != '0')
		{
			if (s[*i] == 'x')
				str = ft_strdup("0x");
			if (s[*i] == 'X')
				str = ft_strdup("0X");
		}
	}
	return (str);
}
