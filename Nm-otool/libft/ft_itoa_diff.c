/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_itoa_diff.c                                     :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 12:54:08 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/23 12:59:27 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

static void	ft_itoto_diff(char *str, long long n)
{
	*str = '0' + n % 10;
	if (n > 9)
		(ft_itoto_diff(--str, n / 10));
}

char		*ft_itoa_diff(long long n)
{
	char			*new;
	long long		nbr;

	if (n < -9223372036854775807)
		return (ft_strdup("-9223372036854775808"));
	nbr = ft_compt_diff(n);
	new = ft_strnew_diff(nbr);
	if (new)
	{
		if (n < 0)
		{
			n = -n;
			new[0] = '-';
		}
		if (n == -2147483648)
		{
			new[nbr - 1] = '8';
			ft_itoto_diff(new + nbr - 2, 214748364);
		}
		else
			ft_itoto_diff(new + nbr - 1, n);
	}
	return (new);
}
