/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_itoa_base.c                                     :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/29 16:13:31 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:05:23 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

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

static char		read_tab(int i)
{
	char	*tab;

	tab = "0123456789abcdefghijklmnopqrstuvwxyz";
	return (tab[i]);
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

char		*ft_itoa_base_sub(int val, int base, int output_size)
{
	char			buffer[output_size + 1];
	char			*p;
	unsigned int	unsigned_val;

	unsigned_val = (unsigned int)val;
	if (base > 36 || base < 2)
		return (NULL);
	buffer[output_size] = 0;
	p = buffer + output_size - 1;
	*p = '0';
	if (unsigned_val > 0)
	{
		while (unsigned_val > 0)
		{
			*p-- = read_tab(unsigned_val % base);
			unsigned_val = unsigned_val / base;
			if (p < buffer)
				break ;
		}
		p++;
	}
	while (p > buffer)
		*--p = '0';
	return (ft_strdup(p));
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
