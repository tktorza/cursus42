/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strjoin.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/29 12:03:15 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:07 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

char		*ft_strjoin(char const *s1, char const *s2)
{
	char	*new;
	int		i;
	int		k;

	if (!s1 || !s2)
		return (NULL);
	i = ft_strlen(s1);
	k = ft_strlen(s2);
	new = malloc(sizeof(int) * (i + k + 1));
	if (!new)
		return (NULL);
	new = ft_strdup(s1);
	new = ft_strcat(new, s2);
	return (new);
}
