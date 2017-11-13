/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   libfts.h                                           :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/07 16:26:09 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/07 16:26:09 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef LIBFTS_H
#define LIBFTS_H

int		ft_isdigit(int c);
int		ft_isalpha(int c);
int		ft_isalnum(int c);
int		ft_isascii(int c);
int		ft_isprint(int c);
int		ft_toupper(int c);
int		ft_tolower(int c);
void	ft_bzero(void *s, size_t n);
int     ft_puts(const char *s);
int     ft_put(char *s);
int     ft_strlen(char *s);
char	*ft_strcat(char *s1, const char *s2);
void	*ft_memset(void *b, int c, size_t len);
void 	*ft_memcpy(void *dst, const void *src, size_t n);
char	*ft_strdup(const char *s1);
void	ft_cat(int fd);

#endif