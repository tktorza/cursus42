/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_itoa.c                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/27 16:51:48 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/03 18:56:10 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

char	*ft_itoa(int n)
{
	char	*new;
	int		nbr;

	nbr = ft_compt(n);
	new = ft_strnew(nbr);
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
			ft_itoto(new + nbr - 2, 214748364);
		}
		else
			ft_itoto(new + nbr - 1, n);
	}
	return (new);
}
