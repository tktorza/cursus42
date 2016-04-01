/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   impress_functions.c                                :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/17 20:37:33 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/27 14:58:18 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

char	*ft_binary(va_list *ap, int modif)
{
	long long c;

	c = va_arg(*ap, long long);
	if (modif == 1)
		return (ft_itoa_base((unsigned short int)c, 2));
	if (modif == 2)
		return (ft_itoa_base((unsigned char)c, 2));
	if (modif == 3)
		return (ft_itoa_base((unsigned long int)c, 2));
	if (modif == 4)
		return (ft_itoa_base((unsigned long long int)c, 2));
	if (modif == 5)
		return (ft_itoa_base((uintmax_t)c, 2));
	if (modif == 6)
		return (ft_itoa_base((ssize_t)c, 2));
	if (modif == 0)
		return (ft_itoa_base((unsigned int)c, 2));
	if (modif == 7)
		return (ft_itoa_base_maj((unsigned long int)c, 2));
	return (0);
}

char	*ft_putvoid(va_list *ap, int modif)
{
	void *p;
	char *str;

	modif = 0;
	p = va_arg(*ap, void *);
	str = ft_itoa_base_bis((long long)p, 16);
	return (str);
}

char	*ft_putlchar(va_list *ap, int modif)
{
	wchar_t	c;
	char	*buf;
	int		i;

	i = 0;
	modif = 0;
	c = (va_arg(*ap, wchar_t));
	buf = ft_spec(ft_itoa_base((unsigned int)c, 2));
	if (c == 0)
		g_total += 1;
	return (buf);
}

char	*ft_putlchar_etoile(va_list *ap, int modif)
{
	wchar_t	*s;
	char	*buf;
	int		i;
	char	*tmp;

	modif = 0;
	i = 0;
	g_x = 0;
	tmp = ft_strnew(1);
	buf = ft_strnew(1);
	g_chars = ft_mallocation();
	s = va_arg(*ap, wchar_t *);
	if (s == NULL)
		return (NULL);
	while (s[i])
	{
		tmp = ft_spec(ft_itoa_base((unsigned int)s[i], 2));
		g_chars[g_x] = ft_compt_char(tmp);
		buf = ft_strjoin(buf, tmp);
		i++;
		g_x += 1;
	}
	g_chars[g_x] = -10;
	return (buf);
}
