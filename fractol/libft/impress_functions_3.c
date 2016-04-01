/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   impress_functions_3.c                              :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 17:50:01 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/23 17:52:35 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

char	*ft_put_hexa(va_list *ap, int modif)
{
	long long c;

	c = va_arg(*ap, long long);
	if (modif == 1)
		return (ft_itoa_base((unsigned short int)c, 16));
	if (modif == 2)
		return (ft_itoa_base((unsigned char)c, 16));
	if (modif == 3)
		return (ft_itoa_base((unsigned long int)c, 16));
	if (modif == 4)
		return (ft_itoa_base((unsigned long long int)c, 16));
	if (modif == 5)
		return (ft_itoa_base((uintmax_t)c, 16));
	if (modif == 6)
		return (ft_itoa_base((ssize_t)c, 16));
	if (modif == 0)
		return (ft_itoa_base((unsigned int)c, 16));
	if (modif == 7)
		return (ft_itoa_base_maj((unsigned long int)c, 16));
	return (0);
}

char	*ft_put_hexa_maj(va_list *ap, int modif)
{
	long long c;

	c = va_arg(*ap, long long);
	if (modif == 1)
		return (ft_itoa_base_maj((unsigned short int)c, 16));
	if (modif == 2)
		return (ft_itoa_base_maj((unsigned char)c, 16));
	if (modif == 3)
		return (ft_itoa_base_maj((unsigned long int)c, 16));
	if (modif == 4)
		return (ft_itoa_base_maj((unsigned long long int)c, 16));
	if (modif == 5)
		return (ft_itoa_base_maj((uintmax_t)c, 16));
	if (modif == 6)
		return (ft_itoa_base_maj((ssize_t)c, 16));
	if (modif == 0)
		return (ft_itoa_base_maj((unsigned int)c, 16));
	return (0);
}
