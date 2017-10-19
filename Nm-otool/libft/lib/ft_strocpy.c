/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strocpy.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/01/07 08:49:14 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:20 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

char		*ft_strocpy(char *str, char c)
{
	int		i;
	int		i_save;
	int		len;
	char	*str_save;

	i = 0;
	len = 0;
	while (str[i] != c)
		i++;
	i++;
	i_save = i;
	while (str[i])
	{
		i++;
		len++;
	}
	str_save = (char *)malloc(sizeof(char) * len);
	str_save = ft_strsub(str, i_save, len);
	return (str_save);
}
