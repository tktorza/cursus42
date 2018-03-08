/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   impress_functions_2.c                              :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 17:47:49 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/27 14:58:34 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

char	*ft_putchar_v(va_list *ap, int modif)
{
	char	*buf;
	char	c;

	if (modif == 3)
		buf = ft_putlchar(ap, modif);
	else
	{
		c = (char)va_arg(*ap, unsigned int);
		if (c == 0)
			g_total += 1;
		buf = ft_strnew(2);
		*buf = c;
	}
	return (buf);
}

char	*ft_putstr_v(va_list *ap, int modif)
{
	if (modif == 3)
		return (ft_putlchar_etoile(ap, modif));
	return (va_arg(*ap, char *));
}

char	*ft_putnbr_v(va_list *ap, int modif)
{
	char	*buf;

	if (modif == 1)
		buf = ft_itoa_diff((short int)va_arg(*ap, long));
	else if (modif == 2)
		buf = ft_itoa_diff((signed char)va_arg(*ap, long));
	else if (modif == 3)
		buf = ft_itoa_diff(va_arg(*ap, long int));
	else if (modif == 4)
		buf = ft_itoa_diff(va_arg(*ap, long long int));
	else if (modif == 5)
		buf = ft_itoa_diff(va_arg(*ap, intmax_t));
	else if (modif == 6)
		buf = ft_itoa_diff(va_arg(*ap, size_t));
	else
		buf = ft_itoa(va_arg(*ap, int));
	return (buf);
}

char	*ft_put_oct(va_list *ap, int modif)
{
	long long c;

	c = va_arg(*ap, long long);
	if (modif == 1)
		return (ft_itoa_base((unsigned short int)c, 8));
	if (modif == 2)
		return (ft_itoa_base((unsigned char)c, 8));
	if (modif == 3)
		return (ft_itoa_base((unsigned long int)c, 8));
	if (modif == 4)
		return (ft_itoa_base((unsigned long long int)c, 8));
	if (modif == 5)
		return (ft_itoa_base((uintmax_t)c, 8));
	if (modif == 6)
		return (ft_itoa_base((ssize_t)c, 8));
	if (modif == 0)
		return (ft_itoa_base((unsigned int)c, 8));
	if (modif == 7)
		return (ft_itoa_base_maj((unsigned long int)c, 8));
	return (0);
}

char	*ft_put_deci(va_list *ap, int modif)
{
	long long c;

	c = va_arg(*ap, long long);
	if (modif == 1)
		return (ft_itoa_base((unsigned short int)c, 10));
	if (modif == 2)
		return (ft_itoa_base((unsigned char)c, 10));
	if (modif == 3)
		return (ft_itoa_base((unsigned long int)c, 10));
	if (modif == 4)
		return (ft_itoa_base((unsigned long long int)c, 10));
	if (modif == 5)
		return (ft_itoa_base((uintmax_t)c, 10));
	if (modif == 6)
		return (ft_itoa_base((ssize_t)c, 10));
	if (modif == 0)
		return (ft_itoa_base((unsigned int)c, 10));
	if (modif == 7)
		return (ft_itoa_base_maj((unsigned long int)c, 10));
	return (0);
}
