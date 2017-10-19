/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_itoa.c                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/29 16:13:31 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:05:24 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

static long	ft_divint(int n)
{
	int		i;

	i = 0;
	while (n != 0)
	{
		n = n / 10;
		i++;
	}
	return (i);
}

static int	ft_neg_itoa(long n)
{
	if (n < 0)
		return (1);
	return (0);
}

static char	*ft_retitoa(int n, int len, char *str, int nb)
{
	if (n < 0)
	{
		nb = -n;
		str[0] = '-';
		while (nb > 9)
		{
			str[len--] = (nb % 10) + '0';
			nb = nb / 10;
		}
	}
	else
	{
		len--;
		while (nb > 9)
		{
			str[len--] = (nb % 10) + '0';
			nb = nb / 10;
		}
	}
	if (nb < 10)
		str[len] = nb + '0';
	return (str);
}

char		*ft_itoa(int n)
{
	char	*str;
	int		len;
	int		nb;
	int		neg;

	if (n == 0)
		return (ft_strdup("0"));
	if (n == -2147483648)
		return (ft_strdup("-2147483648"));
	nb = n;
	neg = 0;
	len = ft_divint(n);
	if (ft_neg_itoa(n) == 1)
		neg = 1;
	str = ft_strnew((len) + neg);
	if (!str)
		return (NULL);
	return (ft_retitoa(n, len, str, nb));
}
