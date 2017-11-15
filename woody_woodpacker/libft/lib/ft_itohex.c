/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_itohex.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: rbenjami <rbenjami@student.42.fr>          +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2013/11/19 12:21:39 by rbenjami          #+#    #+#             */
/*   Updated: 2017/10/18 14:05:25 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../inc/libft.h"
#include <stdlib.h>

static char		*init_string(int len)
{
	int			i;
	char		*str;

	i = 0;
	str = (char *)malloc((sizeof(char) * len) + 1);
	while (i < len)
	{
		str[i] = '0';
		i++;
	}
	str[len] = '\0';
	return (str);
}

static char		*init_hex(void)
{
	char		*tab;

	tab = (char *)malloc(sizeof(char) * 16);
	tab[0] = '0';
	tab[1] = '1';
	tab[2] = '2';
	tab[3] = '3';
	tab[4] = '4';
	tab[5] = '5';
	tab[6] = '6';
	tab[7] = '7';
	tab[8] = '8';
	tab[9] = '9';
	tab[10] = 'a';
	tab[11] = 'b';
	tab[12] = 'c';
	tab[13] = 'd';
	tab[14] = 'e';
	tab[15] = 'f';
	return (tab);
}

char			*ft_itohex(int n, int nb_z)
{
	char		*convert;
	int			res;
	int			i;
	char		*tab;

	tab = init_hex();
	i = 0;
	convert = init_string(nb_z);
	if (n < 0 || nb_z <= 0)
		return (convert);
	while (n > 0)
	{
		res = n % 16;
		convert[((nb_z - 1) - i)] = tab[res];
		n /= 16;
		i++;
	}
	free(tab);
	return (convert);
}
