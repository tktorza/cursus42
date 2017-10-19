/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strmap.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/29 10:41:41 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:10 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

char		*ft_strmap(const char *s, char (*f)(char))
{
	char	*str;
	char	*res;

	if (!s || !f)
		return (NULL);
	res = ft_strnew(ft_strlen(s));
	if (res == NULL)
		return (NULL);
	str = res;
	while (*s)
	{
		*str = (*f)(*s);
		str++;
		s++;
	}
	*str = '\0';
	return (res);
}
