/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_itoa_base.c                                     :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 12:53:33 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/23 12:53:54 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

static int	ft_sizer(unsigned long long *value, int base)
{
	int					tall;
	unsigned long long	bis;

	bis = *value;
	tall = 1;
	while (bis / base != 0)
	{
		bis = bis / base;
		tall++;
	}
	return (tall);
}

char		*ft_itoa_base(unsigned long long value, int base)
{
	char	*s;
	int		tall;

	if (value == 0)
		return (ft_strdup("0"));
	tall = ft_sizer(&value, base);
	s = (char *)malloc(sizeof(char) * tall + 1);
	s[tall] = '\0';
	tall--;
	while (value != 0)
	{
		if (value % base > 9)
			s[tall] = (value % base) + 87;
		else
			s[tall] = (value % base) + 48;
		value = value / base;
		tall--;
	}
	if (tall == 0)
		s[tall] = '-';
	return (s);
}

char		*ft_itoa_base_maj(unsigned long long value, int base)
{
	char	*s;
	int		tall;

	if (value == 0)
		return (ft_strdup("0"));
	tall = ft_sizer(&value, base);
	s = (char *)malloc(sizeof(char) * tall + 1);
	s[tall] = '\0';
	tall--;
	while (value != 0)
	{
		if (value % base > 9)
			s[tall] = (value % base) + 55;
		else
			s[tall] = (value % base) + 48;
		value = value / base;
		tall--;
	}
	if (tall == 0)
		s[tall] = '-';
	return (s);
}
